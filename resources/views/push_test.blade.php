<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
<title>SMASA Push Test</title>
<style>
  body{font-family:Arial,sans-serif;max-width:640px;margin:40px auto;padding:0 20px;background:#f5f5f5}
  h2{color:#2c3e50}
  .card{background:#fff;border-radius:10px;padding:20px;margin:16px 0;box-shadow:0 2px 8px rgba(0,0,0,.1)}
  .row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f0f0f0}
  .row:last-child{border:none}
  .label{color:#666;font-size:14px}
  .val{font-weight:bold;font-size:14px}
  .ok{color:#27ae60}.err{color:#e74c3c}.warn{color:#f39c12}
  button{background:#3498db;color:#fff;border:none;padding:12px 24px;border-radius:8px;font-size:16px;cursor:pointer;width:100%;margin-top:8px}
  button:hover{background:#2980b9}
  button:disabled{background:#bdc3c7;cursor:not-allowed}
  .btn-red{background:#e74c3c}
  .btn-red:hover{background:#c0392b}
  #log{background:#1e1e1e;color:#0f0;font-family:monospace;font-size:12px;padding:12px;border-radius:8px;max-height:320px;overflow-y:auto;white-space:pre-wrap;word-break:break-all}
</style>
</head>
<body>
<h2>🔔 SMASA Push Notification Tester</h2>
<p style="color:#666;font-size:13px">Opens from <code>/push-test</code>. Shows exactly where the push chain breaks.</p>

<div class="card">
  <h3 style="margin-top:0">Status</h3>
  <div class="row"><span class="label">ServiceWorker support</span><span class="val" id="s-sw">…</span></div>
  <div class="row"><span class="label">PushManager support</span><span class="val" id="s-pm">…</span></div>
  <div class="row"><span class="label">Notification permission</span><span class="val" id="s-perm">…</span></div>
  <div class="row"><span class="label">VAPID key (from page meta)</span><span class="val" id="s-vapid">…</span></div>
  <div class="row"><span class="label">CSRF token (from page meta)</span><span class="val" id="s-csrf">…</span></div>
  <div class="row"><span class="label">SW registered</span><span class="val" id="s-sw-reg">…</span></div>
  <div class="row"><span class="label">Push subscription in browser</span><span class="val" id="s-sub">…</span></div>
  <div class="row"><span class="label">Rows in push_subscriptions</span><span class="val" id="s-db">…</span></div>
  <div class="row"><span class="label">Logged in as</span><span class="val" id="s-user">…</span></div>
</div>

<div class="card">
  <button id="btn-sub" onclick="doSubscribe()">▶ Request Permission &amp; Subscribe</button>
  <button id="btn-send" style="background:#27ae60;margin-top:8px" onclick="doSendTest()">📨 Send Test Push Now</button>
  <button id="btn-unsub" class="btn-red" onclick="doUnsubscribe()" style="margin-top:8px">↺ Unsubscribe &amp; Reset</button>
  <button onclick="location.reload()" style="background:#7f8c8d;margin-top:8px">⟳ Refresh checks</button>
  <p style="color:#666;font-size:12px;margin-top:8px">Tip: close this browser tab/app fully after subscribing, then tap Send Test Push from another device/tab to confirm it arrives as a real OS notification.</p>
</div>

<div class="card">
  <h3 style="margin-top:0">Log</h3>
  <div id="log">Starting checks…&#10;</div>
</div>

<script>
var VAPID_KEY = document.querySelector('meta[name="vapid-public-key"]').content;
var CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

function log(msg){
  var el=document.getElementById('log');
  el.textContent+='['+new Date().toLocaleTimeString()+'] '+msg+'\n';
  el.scrollTop=el.scrollHeight;
}
function set(id,txt,cls){
  var el=document.getElementById(id);
  el.textContent=txt; el.className='val '+(cls||'');
}

// ── initial checks ────────────────────────────────────────────────────────────
(function init(){
  set('s-sw', 'serviceWorker' in navigator ? 'YES ✓':'NO ✗', 'serviceWorker' in navigator?'ok':'err');
  set('s-pm', 'PushManager' in window ? 'YES ✓':'NO ✗', 'PushManager' in window?'ok':'err');

  var perm=Notification.permission;
  set('s-perm', perm, perm==='granted'?'ok':perm==='denied'?'err':'warn');
  log('Notification.permission = '+perm);
  if(perm==='denied') log('⚠ Permission BLOCKED. Go to browser site settings and reset notifications for this site.');

  set('s-vapid', VAPID_KEY ? VAPID_KEY.substring(0,20)+'… ✓' : 'MISSING ✗', VAPID_KEY?'ok':'err');
  set('s-csrf',  CSRF_TOKEN ? 'Present ✓':'MISSING ✗', CSRF_TOKEN?'ok':'err');

  // check server state
  log('Fetching /push-diag …');
  fetch('/push-diag',{credentials:'same-origin'})
    .then(r=>r.json()).then(d=>{
      log('push-diag: '+JSON.stringify(d));
      set('s-db', d.all_push_subs_total+' row(s)', d.all_push_subs_total>0?'ok':'warn');
      var u = d.subscriber_type
        ? d.subscriber_type+' id='+d.subscriber_id+(d.has_push_trait?' (trait ✓)':' (trait MISSING ✗)')
        : 'Not resolved (check session)';
      set('s-user', u, d.subscriber_found?'ok':'err');
    }).catch(e=>{ log('push-diag error: '+e.message); set('s-db','Error','err'); });

  // check SW + subscription
  if(!('serviceWorker' in navigator)) return;
  navigator.serviceWorker.register('/sw.js').then(function(reg){
    set('s-sw-reg','registered ✓ scope='+reg.scope,'ok');
    log('SW scope: '+reg.scope);
    return reg.pushManager.getSubscription().then(function(sub){
      if(sub){
        set('s-sub','EXISTS ✓\n'+sub.endpoint.substring(0,55)+'…','ok');
        log('Existing subscription: '+sub.endpoint);
      } else {
        set('s-sub','NONE — click Subscribe','warn');
        log('No existing subscription in browser');
      }
    });
  }).catch(function(e){
    set('s-sw-reg','FAILED: '+e.message,'err');
    log('SW registration FAILED: '+e.message);
  });
})();

// ── subscribe ─────────────────────────────────────────────────────────────────
function doSubscribe(){
  document.getElementById('btn-sub').disabled=true;
  log('--- Subscribe clicked ---');

  function urlBase64ToUint8Array(b64){
    var pad='='.repeat((4-b64.length%4)%4);
    var b=(b64+pad).replace(/-/g,'+').replace(/_/g,'/');
    var raw=atob(b); var arr=new Uint8Array(raw.length);
    for(var i=0;i<raw.length;i++) arr[i]=raw.charCodeAt(i);
    return arr;
  }
  function ab2b64(buf){
    var bytes=new Uint8Array(buf),bin='';
    for(var i=0;i<bytes.byteLength;i++) bin+=String.fromCharCode(bytes[i]);
    return btoa(bin);
  }

  navigator.serviceWorker.register('/sw.js').then(function(reg){
    log('SW ready');
    return Notification.requestPermission().then(function(perm){
      log('Permission: '+perm);
      set('s-perm', perm, perm==='granted'?'ok':perm==='denied'?'err':'warn');
      if(perm!=='granted'){
        log('Permission not granted — cannot subscribe');
        document.getElementById('btn-sub').disabled=false;
        return;
      }
      // unsubscribe existing first so we get a fresh one
      return reg.pushManager.getSubscription().then(function(old){
        if(old) { log('Removing old subscription first'); return old.unsubscribe(); }
      }).then(function(){
        log('Calling pushManager.subscribe() …');
        return reg.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: urlBase64ToUint8Array(VAPID_KEY)
        });
      }).then(function(sub){
        log('Subscription created: '+sub.endpoint.substring(0,60)+'…');
        set('s-sub','Created ✓','ok');
        var key=sub.getKey('p256dh'), token=sub.getKey('auth');
        var enc=(PushManager.supportedContentEncodings||['aesgcm'])[0];
        var payload={
          endpoint: sub.endpoint,
          key:   key   ? ab2b64(key)   : null,
          token: token ? ab2b64(token) : null,
          contentEncoding: enc
        };
        log('Posting payload to /notifications/push/subscribe …');
        return fetch('/notifications/push/subscribe',{
          method:'POST',
          headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
          credentials:'same-origin',
          body:JSON.stringify(payload)
        });
      }).then(function(res){
        log('Server HTTP '+res.status);
        return res.text().then(function(body){
          log('Server body: '+body);
          if(res.ok){
            log('✅ SUCCESS — subscription saved! Refreshing DB count …');
            fetch('/push-diag',{credentials:'same-origin'}).then(r=>r.json()).then(d=>{
              set('s-db', d.all_push_subs_total+' row(s) ✓', 'ok');
              log('push_subscriptions now has '+d.all_push_subs_total+' row(s)');
            });
          } else {
            log('❌ Server rejected — see body above');
          }
          document.getElementById('btn-sub').disabled=false;
        });
      }).catch(function(err){
        log('ERROR: '+err.name+': '+err.message);
        document.getElementById('btn-sub').disabled=false;
      });
    });
  });
}

// ── send test push ───────────────────────────────────────────────────────────
function doSendTest(){
  document.getElementById('btn-send').disabled=true;
  log('--- Send Test Push clicked ---');
  fetch('/push-send-test',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
    credentials:'same-origin'
  }).then(function(res){
    return res.json().then(function(body){
      log('Server HTTP '+res.status+': '+JSON.stringify(body));
      if(res.ok && body.success){
        log('✅ '+body.message);
      } else {
        log('❌ '+(body.message||'Send failed'));
      }
      document.getElementById('btn-send').disabled=false;
    });
  }).catch(function(err){
    log('ERROR sending test push: '+err.message);
    document.getElementById('btn-send').disabled=false;
  });
}

// ── unsubscribe ───────────────────────────────────────────────────────────────
function doUnsubscribe(){
  log('--- Unsubscribing ---');
  navigator.serviceWorker.getRegistrations().then(function(regs){
    return Promise.all(regs.map(function(r){
      return r.pushManager.getSubscription().then(function(s){ if(s) return s.unsubscribe(); }).then(function(){ return r.unregister(); });
    }));
  }).then(function(){
    log('Done. SW unregistered. Now click Subscribe.');
    set('s-sub','Unsubscribed','warn'); set('s-sw-reg','Unregistered','warn');
  });
}
</script>
</body>
</html>