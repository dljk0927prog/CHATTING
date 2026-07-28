<?php
$roomId = (int)($roomId ?? 0);
$callType = $callType ?? 'voice';
$receiverId = (int)($receiverId ?? 0);
$partnerName = $partnerName ?? '';
$isIncoming = !empty($isIncoming);
$currentUserId = (int)($user['id'] ?? 0);
// 部署到 cPanel 时若不在 /Chat_System 下，可在 config 中定义 BASE_URL，或由当前请求自动推断
if (!empty($_SERVER['REQUEST_URI'])) {
    $baseUrl = preg_replace('#/chat/.*$#', '', rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '', '/')) ?: '/Chat_System';
} else {
    $baseUrl = defined('BASE_URL') ? BASE_URL : '/Chat_System';
}
$isSecureContext = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == '443' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

// 从配置读取 ICE 服务器（STUN + 可选 TURN），跨设备/跨网络必须配置 TURN 才能互通
$callConfigFile = defined('BASE_PATH') ? BASE_PATH . '/config/call-config.php' : (dirname(__DIR__, 2) . '/config/call-config.php');
$callConfig = @include($callConfigFile);
$iceServers = isset($callConfig['webrtc']['ice_servers']) && is_array($callConfig['webrtc']['ice_servers'])
    ? $callConfig['webrtc']['ice_servers']
    : [
        ['urls' => 'stun:stun.l.google.com:19302'],
        ['urls' => 'stun:stun1.l.google.com:19302'],
        ['urls' => 'stun:stun2.l.google.com:19302'],
        ['urls' => 'stun:stun.stunprotocol.org:3478']
    ];
$meteredTurnUrl = isset($callConfig['turn']['metered_api_url']) ? trim((string)$callConfig['turn']['metered_api_url']) : '';
// 前端不直连 Metered（会 CORS），改为请求同源代理
$turnCredentialsUrl = $meteredTurnUrl !== '' ? (rtrim($baseUrl, '/') . '/chat/getTurnCredentials') : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $callType === 'video' ? '视频' : '语音'; ?>通话 - <?php echo htmlspecialchars($partnerName); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        .call-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .call-container h2 {
            margin-bottom: 8px;
            font-size: 1.4rem;
        }
        .partner-name {
            color: #666;
            font-size: 1rem;
            margin-bottom: 24px;
        }
        .status {
            margin: 16px 0;
            font-size: 1rem;
            color: #555;
            min-height: 24px;
        }
        .call-timer {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin: 8px 0;
        }
        .call-controls {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 24px;
        }
        .call-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .call-btn:hover { transform: scale(1.08); }
        .call-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .btn-start {
            background: linear-gradient(135deg, #2ed573, #26c765);
            color: white;
        }
        .btn-end {
            background: linear-gradient(135deg, #ff4757, #ee5a6f);
            color: white;
        }
        .btn-mute {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        .btn-mute.muted { background: linear-gradient(135deg, #e67e22, #d35400); }
        .remote-audio { display: none; }
        /* 来电弹窗 */
        .incoming-modal {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.75);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .incoming-content {
            background: white;
            border-radius: 20px;
            padding: 32px;
            text-align: center;
            max-width: 340px;
            width: 90%;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        .incoming-content .partner-name { margin-bottom: 20px; }
        .incoming-actions {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 24px;
        }
        .incoming-actions .call-btn { width: 64px; height: 64px; font-size: 26px; }
    </style>
</head>
<body>
    <div class="call-container">
        <h2><?php echo $callType === 'video' ? '视频' : '语音'; ?>通话</h2>
        <div class="partner-name" id="partnerName"><?php echo htmlspecialchars($partnerName); ?></div>
        <div class="status" id="status"><?php echo $isIncoming ? '等待接听...' : '准备呼叫'; ?></div>
        <div class="call-timer" id="callTimer" style="display:none;">00:00</div>
        <?php if (!$isSecureContext): ?>
        <p class="insecure-warning" id="insecureWarning" style="margin-top:8px;font-size:12px;color:#c0392b;">提示：当前为非 HTTPS 环境，麦克风可能被浏览器限制，部署到 cPanel 请使用 HTTPS。</p>
        <?php endif; ?>
        <p class="turn-tip" style="margin-top:6px;font-size:11px;color:#7f8c8d;">若手机与电脑无法互通，请在 config/call-config.php 中配置 TURN 中继服务器。</p>
        <div class="call-controls">
            <?php if ($callType === 'voice'): ?>
            <button type="button" class="call-btn btn-mute" id="btnMute" title="静音" style="display:none;"><i class="fas fa-microphone"></i></button>
            <?php endif; ?>
            <button type="button" class="call-btn btn-start" id="btnStart" title="开始通话"> <i class="fas fa-phone"></i> </button>
            <button type="button" class="call-btn btn-end" id="btnEnd" title="挂断" style="display:none;"> <i class="fas fa-phone-slash"></i> </button>
        </div>
        <p style="margin-top: 20px; font-size: 14px;"><a href="/Chat_System/chat/room?id=<?php echo $roomId; ?>" style="color: #667eea;">返回聊天</a></p>
    </div>

    <div class="incoming-modal" id="incomingModal">
        <div class="incoming-content">
            <div class="partner-name" id="incomingPartnerName"><?php echo htmlspecialchars($partnerName); ?> 邀请您<?php echo $callType === 'video' ? '视频' : '语音'; ?>通话</div>
            <div class="incoming-actions">
                <button type="button" class="call-btn btn-end" id="btnDecline"><i class="fas fa-phone-slash"></i></button>
                <button type="button" class="call-btn btn-start" id="btnAccept"><i class="fas fa-phone"></i></button>
            </div>
        </div>
    </div>

    <audio id="remote-audio" autoplay playsinline></audio>

    <script>
(function() {
        const BASE = '<?php echo addslashes($baseUrl); ?>';
        const ROOM_ID = <?php echo $roomId; ?>;
        const RECEIVER_ID = <?php echo $receiverId; ?>;
        const IS_INCOMING = <?php echo $isIncoming ? 'true' : 'false'; ?>;
        const ICE_SERVERS = <?php echo json_encode($iceServers); ?>;
        const TURN_CREDENTIALS_URL = <?php echo json_encode($turnCredentialsUrl); ?>;

        let iceServersResolved = ICE_SERVERS;

        async function ensureIceServers() {
            if (!TURN_CREDENTIALS_URL) return;
            try {
                const r = await fetch(TURN_CREDENTIALS_URL);
                const data = await r.json();
                const arr = Array.isArray(data) ? data : (data && data.iceServers);
                if (Array.isArray(arr) && arr.length > 0) {
                    iceServersResolved = arr;
                }
            } catch (e) {
                console.warn('TURN 凭证获取失败，使用 STUN', e);
            }
        }

        let pc = null;
        let localStream = null;
        let pollTimer = null;
        let timerInterval = null;
        let pendingOffer = null;
        let isMuted = false;

        const statusEl = document.getElementById('status');
        const callTimerEl = document.getElementById('callTimer');
        const btnStart = document.getElementById('btnStart');
        const btnEnd = document.getElementById('btnEnd');
        const btnMute = document.getElementById('btnMute');
        const incomingModal = document.getElementById('incomingModal');
        const btnAccept = document.getElementById('btnAccept');
        const btnDecline = document.getElementById('btnDecline');

        function setStatus(text) {
            statusEl.textContent = text;
        }

        function showIncoming() {
            incomingModal.style.display = 'flex';
        }
        function hideIncoming() {
            incomingModal.style.display = 'none';
        }

        async function sendSignal(type, payload) {
            const form = new FormData();
            form.append('type', type);
            form.append('payload', typeof payload === 'string' ? payload : JSON.stringify(payload));
            try {
                const url = `${BASE}/callSignal/push?room_id=${ROOM_ID}&receiver_id=${RECEIVER_ID}`;
                const r = await fetch(url, { method: 'POST', body: form });
                const data = await r.json();
                if (!data.success) console.error('信令发送失败:', data.error);
            } catch (e) {
                console.error('sendSignal error', e);
            }
        }

        async function createPeerConnection() {
            if (pc) return;
            pc = new RTCPeerConnection({ iceServers: iceServersResolved });
            pc.ontrack = function(e) {
                const remoteAudio = document.getElementById('remote-audio');
                remoteAudio.srcObject = e.streams[0];
                remoteAudio.style.display = 'block';
                setStatus('通话中');
            };
            pc.onicecandidate = function(e) {
                if (e.candidate) sendSignal('ice', e.candidate);
            };
            if (!localStream) {
                localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            }
            localStream.getTracks().forEach(function(t) {
                pc.addTrack(t, localStream);
            });
        }

        function startPolling() {
            if (pollTimer) return;
            pollTimer = setInterval(async function() {
                try {
                    const r = await fetch(`${BASE}/callSignal/poll?room_id=${ROOM_ID}`);
                    const data = await r.json();
                    if (!data.success || !Array.isArray(data.signals)) return;
                    for (const s of data.signals) {
                        const payload = JSON.parse(s.payload);
                        if (s.signal_type === 'offer') {
                            pendingOffer = payload;
                            showIncoming();
                        } else if (s.signal_type === 'answer') {
                            if (pc) {
                                await pc.setRemoteDescription(new RTCSessionDescription(payload));
                                setStatus('通话中');
                                startTimer();
                            }
                        } else if (s.signal_type === 'ice') {
                            if (pc) {
                                try {
                                    await pc.addIceCandidate(new RTCIceCandidate(payload));
                                } catch (err) {
                                    console.warn('addIceCandidate', err);
                                }
                            }
                        } else if (s.signal_type === 'end') {
                            endCall(false);
                        }
                    }
                } catch (e) {
                    console.error('poll error', e);
                }
            }, 2000);
        }

        function startTimer() {
            callTimerEl.style.display = 'block';
            let sec = 0;
            clearInterval(timerInterval);
            timerInterval = setInterval(function() {
                sec++;
                const m = String(Math.floor(sec / 60)).padStart(2, '0');
                const s = String(sec % 60).padStart(2, '0');
                callTimerEl.textContent = m + ':' + s;
            }, 1000);
        }

        async function startCall() {
            try {
                setStatus('获取麦克风...');
                await ensureIceServers();
                await createPeerConnection();
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                await sendSignal('offer', offer);
                startPolling();
                setStatus('等待对方接听...');
                btnStart.style.display = 'none';
                if (btnMute) btnMute.style.display = 'flex';
                btnEnd.style.display = 'inline-flex';
            } catch (e) {
                console.error('startCall', e);
                setStatus('启动失败: ' + e.message);
            }
        }

        async function handleAccept() {
            if (!pendingOffer) return;
            setStatus('连接中...');
            await ensureIceServers();
            await createPeerConnection();
            await pc.setRemoteDescription(new RTCSessionDescription(pendingOffer));
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            await sendSignal('answer', answer);
            pendingOffer = null;
            hideIncoming();
            setStatus('通话中');
            startTimer();
            btnStart.style.display = 'none';
            if (btnMute) btnMute.style.display = 'flex';
            btnEnd.style.display = 'inline-flex';
        }

        function endCall(localEnd) {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
            if (pc) {
                pc.close();
                pc = null;
            }
            if (localStream) {
                localStream.getTracks().forEach(function(t) { t.stop(); });
                localStream = null;
            }
            clearInterval(timerInterval);
            setStatus('通话已结束');
            hideIncoming();
            callTimerEl.style.display = 'none';
            callTimerEl.textContent = '00:00';
            btnStart.style.display = 'inline-flex';
            if (btnMute) btnMute.style.display = 'none';
            btnEnd.style.display = 'none';
            document.getElementById('remote-audio').style.display = 'none';
            if (localEnd) {
                fetch(`${BASE}/callSignal/end?room_id=${ROOM_ID}`, { method: 'POST' }).catch(function() {});
            }
            setTimeout(function() {
                setStatus(IS_INCOMING ? '等待接听...' : '准备呼叫');
            }, 2000);
        }

        btnStart.addEventListener('click', startCall);
        btnEnd.addEventListener('click', function() { endCall(true); });
        if (btnMute) {
            btnMute.addEventListener('click', function() {
                if (!localStream) return;
                isMuted = !isMuted;
                localStream.getAudioTracks().forEach(function(t) { t.enabled = !isMuted; });
                btnMute.classList.toggle('muted', isMuted);
                btnMute.querySelector('i').className = isMuted ? 'fas fa-microphone-slash' : 'fas fa-microphone';
            });
        }
        btnAccept.addEventListener('click', handleAccept);
        btnDecline.addEventListener('click', function() {
            pendingOffer = null;
            hideIncoming();
            sendSignal('end', {});
            setStatus('已拒绝');
            setTimeout(function() { setStatus('准备呼叫'); }, 2000);
        });

        if (IS_INCOMING) {
            startPolling();
            btnStart.style.display = 'none';
        }
})();
    </script>
</body>
</html>
