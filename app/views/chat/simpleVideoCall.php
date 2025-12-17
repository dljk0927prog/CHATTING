<?php
session_start();
require_once '../../config/Database.php';
require_once '../models/User.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;

if ($room_id <= 0 || $receiver_id <= 0) {
    die('Invalid room or receiver ID');
}

// 获取用户信息
$db = Database::getInstance();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("SELECT username, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT username, avatar FROM users WHERE id = ?");
$stmt->execute([$receiver_id]);
$receiver = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>语音通话 - <?php echo htmlspecialchars($receiver['username']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .call-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            font-weight: bold;
        }

        .avatar img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-name {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .call-status {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .call-controls {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .call-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .call-btn:hover {
            transform: scale(1.1);
        }

        .btn-start {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
        }

        .btn-end {
            background: linear-gradient(135deg, #f44336, #da190b);
            color: white;
        }

        .btn-mute {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
        }

        .btn-mute.muted {
            background: linear-gradient(135deg, #FF9800, #F57C00);
        }

        .call-timer {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-top: 20px;
        }

        .remote-audio {
            display: none;
        }

        /* 来电弹窗 */
        .incoming-call-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .incoming-call-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 350px;
            width: 90%;
        }

        .incoming-call-content .avatar {
            width: 100px;
            height: 100px;
            font-size: 36px;
        }

        .incoming-call-content .user-name {
            font-size: 20px;
            margin: 15px 0;
        }

        .incoming-call-controls {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }

        .btn-accept {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
        }

        .btn-decline {
            background: linear-gradient(135deg, #f44336, #da190b);
            color: white;
        }

        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="call-container">
        <div class="avatar">
            <?php if ($receiver['avatar'] && $receiver['avatar'] !== 'default_avatar.png'): ?>
                <img src="../../public/uploads/avatars/<?php echo htmlspecialchars($receiver['avatar']); ?>" alt="Avatar">
            <?php else: ?>
                <?php echo strtoupper(substr($receiver['username'], 0, 1)); ?>
            <?php endif; ?>
        </div>
        
        <div class="user-name"><?php echo htmlspecialchars($receiver['username']); ?></div>
        <div class="call-status" id="call-status">准备通话</div>
        
        <div class="call-controls">
            <button class="call-btn btn-start" id="btn-start-call" title="开始通话">
                <i class="fas fa-phone"></i>
            </button>
            <button class="call-btn btn-end hidden" id="btn-end-call" title="结束通话">
                <i class="fas fa-phone-slash"></i>
            </button>
            <button class="call-btn btn-mute hidden" id="btn-mute" title="静音">
                <i class="fas fa-microphone"></i>
            </button>
        </div>
        
        <div class="call-timer hidden" id="call-timer">00:00</div>
    </div>

    <!-- 远程音频 -->
    <audio class="remote-audio" id="remote-audio" autoplay></audio>

    <!-- 来电弹窗 -->
    <div class="incoming-call-modal" id="incomingCallModal">
        <div class="incoming-call-content">
            <div class="avatar">
                <?php if ($current_user['avatar'] && $current_user['avatar'] !== 'default_avatar.png'): ?>
                    <img src="../../public/uploads/avatars/<?php echo htmlspecialchars($current_user['avatar']); ?>" alt="Avatar">
                <?php else: ?>
                    <?php echo strtoupper(substr($current_user['username'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div class="user-name">来电中...</div>
            <div class="incoming-call-controls">
                <button class="call-btn btn-accept" id="btn-accept-call">
                    <i class="fas fa-phone"></i>
                </button>
                <button class="call-btn btn-decline" id="btn-decline-call">
                    <i class="fas fa-phone-slash"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // 基于成功例子的简化WebRTC实现
        (function() {
            const btnStart = document.getElementById('btn-start-call');
            const btnEnd = document.getElementById('btn-end-call');
            const btnMute = document.getElementById('btn-mute');
            const statusEl = document.getElementById('call-status');
            const remoteAudio = document.getElementById('remote-audio');
            const callTimerEl = document.getElementById('call-timer');
            const incomingModal = document.getElementById('incomingCallModal');

            let pc = null;
            let localStream = null;
            let pollTimer = null;
            let timerInterval = null;
            let isMuted = false;
            let pendingOffer = null;

            const roomId = <?php echo $room_id; ?>;
            const receiverId = <?php echo $receiver_id; ?>;
            const currentUserId = <?php echo $user_id; ?>;

            function setStatus(text, show) {
                statusEl.textContent = text || '';
                statusEl.style.display = show ? 'block' : 'none';
            }

            function showElement(element, show) {
                element.classList.toggle('hidden', !show);
            }

            async function createPeerConnection() {
                console.log('创建WebRTC连接...');
                pc = new RTCPeerConnection({
                    iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
                });

                pc.ontrack = (e) => {
                    console.log('收到远程音频流');
                    remoteAudio.srcObject = e.streams[0];
                    remoteAudio.style.display = 'block';
                };

                pc.onicecandidate = async (e) => {
                    if (e.candidate) {
                        console.log('发送ICE候选');
                        await sendSignal('ice', e.candidate);
                    }
                };

                if (!localStream) {
                    console.log('获取本地音频流...');
                    localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                }
                localStream.getTracks().forEach(t => pc.addTrack(t, localStream));
            }

            async function sendSignal(type, payload) {
                try {
                    const formData = new FormData();
                    formData.append('type', type);
                    formData.append('payload', JSON.stringify(payload));

                    const response = await fetch(`../../app/controllers/CallSignalController.php?action=push&room_id=${roomId}&receiver_id=${receiverId}`, {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    if (!result.success) {
                        console.error('发送信令失败:', result.error);
                    }
                } catch (error) {
                    console.error('发送信令错误:', error);
                }
            }

            async function startCall() {
                console.log('开始通话...');
                showElement(btnStart, false);
                showElement(btnEnd, true);
                showElement(btnMute, true);
                setStatus('呼叫中...', true);

                await createPeerConnection();
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                await sendSignal('offer', offer);

                startPolling();
                startTimer();
            }

            async function handleOffer(payload) {
                console.log('收到来电');
                pendingOffer = payload;
                incomingModal.style.display = 'flex';
            }

            async function handleAnswer(payload) {
                console.log('收到应答');
                if (pc) {
                    await pc.setRemoteDescription(new RTCSessionDescription(payload));
                    setStatus('通话中', true);
                }
            }

            async function handleIce(payload) {
                if (pc) {
                    try {
                        await pc.addIceCandidate(new RTCIceCandidate(payload));
                        console.log('添加ICE候选成功');
                    } catch (e) {
                        console.warn('添加ICE候选失败:', e);
                    }
                }
            }

            function startTimer() {
                let seconds = 0;
                clearInterval(timerInterval);
                timerInterval = setInterval(() => {
                    seconds += 1;
                    const m = String(Math.floor(seconds/60)).padStart(2,'0');
                    const s = String(seconds%60).padStart(2,'0');
                    callTimerEl.textContent = `${m}:${s}`;
                }, 1000);
                showElement(callTimerEl, true);
            }

            function endCall(localEnd) {
                console.log('结束通话');
                if (pollTimer) { 
                    clearInterval(pollTimer); 
                    pollTimer = null; 
                }
                if (pc) { 
                    pc.close(); 
                    pc = null; 
                }
                if (localStream) { 
                    localStream.getTracks().forEach(t => t.stop()); 
                    localStream = null; 
                }
                clearInterval(timerInterval);
                
                showElement(btnStart, true);
                showElement(btnEnd, false);
                showElement(btnMute, false);
                showElement(callTimerEl, false);
                setStatus('通话已结束', true);
                incomingModal.style.display = 'none';

                if (localEnd) {
                    sendSignal('end', {});
                }

                setTimeout(() => {
                    setStatus('准备通话', false);
                }, 2000);
            }

            function startPolling() {
                if (pollTimer) return;
                console.log('开始轮询信令...');
                pollTimer = setInterval(async () => {
                    try {
                        const response = await fetch(`../../app/controllers/CallSignalController.php?action=poll&room_id=${roomId}`);
                        const data = await response.json();
                        if (data.success && Array.isArray(data.signals)) {
                            for (const s of data.signals) {
                                const payload = JSON.parse(s.payload);
                                if (s.signal_type === 'offer') { 
                                    await handleOffer(payload); 
                                }
                                else if (s.signal_type === 'answer') { 
                                    await handleAnswer(payload); 
                                }
                                else if (s.signal_type === 'ice') { 
                                    await handleIce(payload); 
                                }
                                else if (s.signal_type === 'end') { 
                                    endCall(false); 
                                }
                            }
                        }
                    } catch (e) {
                        console.error('轮询信令错误:', e);
                    }
                }, 2000);
            }

            // 事件监听器
            btnStart.addEventListener('click', startCall);
            btnEnd.addEventListener('click', () => endCall(true));
            
            btnMute.addEventListener('click', () => {
                if (!localStream) return;
                isMuted = !isMuted;
                localStream.getAudioTracks().forEach(t => t.enabled = !isMuted);
                btnMute.classList.toggle('muted', isMuted);
                btnMute.querySelector('i').className = isMuted ? 'fas fa-microphone-slash' : 'fas fa-microphone';
            });

            // 接听来电
            document.getElementById('btn-accept-call').addEventListener('click', async () => {
                if (!pendingOffer) return;
                console.log('接听来电');
                
                setStatus('连接中...', true);
                await createPeerConnection();
                await pc.setRemoteDescription(new RTCSessionDescription(pendingOffer));
                
                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);
                await sendSignal('answer', answer);
                
                pendingOffer = null;
                showElement(btnStart, false);
                showElement(btnEnd, true);
                showElement(btnMute, true);
                setStatus('通话中', true);
                startTimer();
                incomingModal.style.display = 'none';
            });

            // 拒绝来电
            document.getElementById('btn-decline-call').addEventListener('click', async () => {
                console.log('拒绝来电');
                pendingOffer = null;
                await sendSignal('end', {});
                incomingModal.style.display = 'none';
                setStatus('已拒绝来电', true);
                setTimeout(() => setStatus('准备通话', false), 2000);
            });

            // 开始轮询
            startPolling();

            // 页面卸载时清理
            window.addEventListener('beforeunload', () => {
                if (pc) {
                    endCall(true);
                }
            });

        })();
    </script>
</body>
</html>
