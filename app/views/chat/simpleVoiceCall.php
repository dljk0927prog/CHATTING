<?php
define('BASE_PATH', dirname(dirname(dirname(__DIR__))));
require_once BASE_PATH . '/core/session.php';
ensureSessionStarted();

// 添加错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../../config/Database.php';
} catch (Exception $e) {
    die('Database error: ' . $e->getMessage());
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$room_id = isset($_GET['roomId']) ? (int)$_GET['roomId'] : 0;
$call_type = isset($_GET['callType']) ? $_GET['callType'] : 'voice';

if ($room_id <= 0) {
    die('Invalid room ID');
}

// 获取用户信息
$db = Database::getInstance();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("SELECT username, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();

// 获取房间信息
$stmt = $pdo->prepare("SELECT cr.*, u.username as creator_name FROM chat_rooms cr LEFT JOIN users u ON cr.created_by = u.id WHERE cr.id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    die('Room not found');
}

// 获取房间成员
$stmt = $pdo->prepare("SELECT u.id, u.username, u.avatar FROM users u INNER JOIN chat_room_members crm ON u.id = crm.user_id WHERE crm.room_id = ? AND u.id != ?");
$stmt->execute([$room_id, $user_id]);
$participants = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>语音通话 - <?php echo htmlspecialchars($room['name'] ?: '房间' . $room_id); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #1a1a1a;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .call-container {
            display: flex;
            height: 100vh;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #2d2d2d;
        }

        .video-area {
            flex: 1;
            background: #1a1a1a;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .local-video {
            width: 200px;
            height: 150px;
            background: #333;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #666;
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .remote-video {
            width: 100%;
            height: 100%;
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #666;
        }

        .call-controls {
            background: #2d2d2d;
            padding: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
            border-top: 1px solid #444;
        }

        .control-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s ease;
        }

        .control-btn:hover {
            transform: scale(1.1);
        }

        .btn-mic {
            background: #4CAF50;
            color: white;
        }

        .btn-mic.muted {
            background: #f44336;
        }

        .btn-camera {
            background: #2196F3;
            color: white;
        }

        .btn-camera.off {
            background: #f44336;
        }

        .btn-end {
            background: #f44336;
            color: white;
        }

        .btn-screen {
            background: #FF9800;
            color: white;
        }

        .btn-settings {
            background: #9C27B0;
            color: white;
        }

        .btn-volume {
            background: #607D8B;
            color: white;
        }

        .sidebar {
            width: 300px;
            background: #333;
            padding: 20px;
            overflow-y: auto;
        }

        .sidebar-section {
            margin-bottom: 30px;
        }

        .sidebar-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #fff;
        }

        .participant {
            display: flex;
            align-items: center;
            padding: 10px;
            background: #444;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .participant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
            color: white;
        }

        .participant-info {
            flex: 1;
        }

        .participant-name {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .participant-status {
            font-size: 12px;
            color: #4CAF50;
        }

        .participant-controls {
            display: flex;
            gap: 5px;
        }

        .participant-btn {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .btn-mic-small {
            background: #4CAF50;
            color: white;
        }

        .btn-mic-small.muted {
            background: #f44336;
        }

        .call-info {
            background: #444;
            padding: 15px;
            border-radius: 8px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-label {
            color: #ccc;
        }

        .info-value {
            color: #fff;
            font-weight: 600;
        }

        .call-status {
            text-align: center;
            padding: 10px;
            background: #4CAF50;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .call-timer {
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .settings-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .toggle-switch {
            position: relative;
            width: 50px;
            height: 25px;
            background: #666;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .toggle-switch.active {
            background: #4CAF50;
        }

        .toggle-switch::after {
            content: '';
            position: absolute;
            width: 21px;
            height: 21px;
            background: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: transform 0.3s;
        }

        .toggle-switch.active::after {
            transform: translateX(25px);
        }

        .hidden {
            display: none !important;
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
            background: #333;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .incoming-call-content .participant-avatar {
            width: 80px;
            height: 80px;
            font-size: 32px;
            margin: 0 auto 20px;
        }

        .incoming-call-controls {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }

        .btn-accept {
            background: #4CAF50;
            color: white;
        }

        .btn-decline {
            background: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="call-container">
        <div class="main-content">
            <div class="video-area">
                <div class="local-video">
                    <i class="fas fa-user"></i>
                </div>
                <div class="remote-video" id="remote-video">
                    <i class="fas fa-video-slash"></i>
                </div>
            </div>
            
            <div class="call-controls">
                <button class="control-btn btn-mic" id="btn-mic" title="麦克风">
                    <i class="fas fa-microphone"></i>
                </button>
                <button class="control-btn btn-camera" id="btn-camera" title="摄像头">
                    <i class="fas fa-video"></i>
                </button>
                <button class="control-btn btn-end" id="btn-end" title="结束通话">
                    <i class="fas fa-phone-slash"></i>
                </button>
                <button class="control-btn btn-screen" id="btn-screen" title="屏幕共享">
                    <i class="fas fa-desktop"></i>
                </button>
                <button class="control-btn btn-settings" id="btn-settings" title="设置">
                    <i class="fas fa-cog"></i>
                </button>
                <button class="control-btn btn-volume" id="btn-volume" title="音量">
                    <i class="fas fa-volume-up"></i>
                </button>
            </div>
        </div>

        <div class="sidebar">
            <div class="call-status" id="call-status">已连接</div>
            <div class="call-timer" id="call-timer">00:00</div>

            <div class="sidebar-section">
                <div class="sidebar-title">参与者</div>
                <div id="participants-list">
                    <?php foreach ($participants as $participant): ?>
                    <div class="participant">
                        <div class="participant-avatar">
                            <?php if ($participant['avatar'] && $participant['avatar'] !== 'default_avatar.png'): ?>
                                <img src="../../public/uploads/avatars/<?php echo htmlspecialchars($participant['avatar']); ?>" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <?php echo strtoupper(substr($participant['username'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="participant-info">
                            <div class="participant-name"><?php echo htmlspecialchars($participant['username']); ?></div>
                            <div class="participant-status">online</div>
                        </div>
                        <div class="participant-controls">
                            <button class="participant-btn btn-mic-small" title="麦克风">
                                <i class="fas fa-microphone"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-title">通话设置</div>
                <div class="settings-item">
                    <span>自动接听</span>
                    <div class="toggle-switch" id="auto-answer"></div>
                </div>
                <div class="settings-item">
                    <span>静音入会</span>
                    <div class="toggle-switch" id="mute-on-join"></div>
                </div>
                <div class="settings-item">
                    <span>调试信息</span>
                    <div class="toggle-switch" id="debug-info"></div>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-title">通话信息</div>
                <div class="call-info">
                    <div class="info-item">
                        <span class="info-label">房间ID:</span>
                        <span class="info-value"><?php echo $room_id; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">通话类型:</span>
                        <span class="info-value">语音通话</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">开始时间:</span>
                        <span class="info-value" id="start-time">--:--</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 远程音频 -->
    <audio id="remote-audio" autoplay style="display: none;"></audio>

    <!-- 来电弹窗 -->
    <div class="incoming-call-modal" id="incomingCallModal">
        <div class="incoming-call-content">
            <div class="participant-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div style="font-size: 20px; margin-bottom: 10px;">来电中...</div>
            <div class="incoming-call-controls">
                <button class="control-btn btn-accept" id="btn-accept-call">
                    <i class="fas fa-phone"></i>
                </button>
                <button class="control-btn btn-decline" id="btn-decline-call">
                    <i class="fas fa-phone-slash"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // 基于成功例子的简化WebRTC实现
        (function() {
            const btnMic = document.getElementById('btn-mic');
            const btnEnd = document.getElementById('btn-end');
            const statusEl = document.getElementById('call-status');
            const remoteAudio = document.getElementById('remote-audio');
            const callTimerEl = document.getElementById('call-timer');
            const incomingModal = document.getElementById('incomingCallModal');
            const startTimeEl = document.getElementById('start-time');

            let pc = null;
            let localStream = null;
            let pollTimer = null;
            let timerInterval = null;
            let isMuted = false;
            let pendingOffer = null;
            let callStartTime = null;

            const roomId = <?php echo $room_id; ?>;
            const currentUserId = <?php echo $user_id; ?>;

            function setStatus(text) {
                statusEl.textContent = text;
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
                    setStatus('通话中');
                };

                pc.onicecandidate = async (e) => {
                    if (e.candidate) {
                        console.log('发送ICE候选');
                        await sendSignal('ice', e.candidate);
                    }
                };

                if (!localStream) {
                    console.log('获取本地音频流...');
                    try {
                        localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        console.log('本地音频流获取成功');
                    } catch (error) {
                        console.error('获取音频流失败:', error);
                        setStatus('麦克风访问被拒绝');
                        return;
                    }
                }
                localStream.getTracks().forEach(t => pc.addTrack(t, localStream));
            }

            async function sendSignal(type, payload) {
                try {
                    const formData = new FormData();
                    formData.append('type', type);
                    formData.append('payload', JSON.stringify(payload));

                    const response = await fetch(`../../app/controllers/CallSignalController.php?action=push&room_id=${roomId}&receiver_id=${currentUserId}`, {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    if (!result.success) {
                        console.error('发送信令失败:', result.error);
                    } else {
                        console.log('信令发送成功:', type);
                    }
                } catch (error) {
                    console.error('发送信令错误:', error);
                }
            }

            async function startCall() {
                console.log('开始通话...');
                setStatus('呼叫中...');

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
                    setStatus('通话中');
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
                callStartTime = new Date();
                let seconds = 0;
                clearInterval(timerInterval);
                timerInterval = setInterval(() => {
                    seconds += 1;
                    const m = String(Math.floor(seconds/60)).padStart(2,'0');
                    const s = String(seconds%60).padStart(2,'0');
                    callTimerEl.textContent = `${m}:${s}`;
                    
                    const now = new Date();
                    const startTime = now.toLocaleTimeString('zh-CN', {hour12: false}).substr(0, 5);
                    startTimeEl.textContent = startTime;
                }, 1000);
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
                
                setStatus('通话已结束');
                incomingModal.style.display = 'none';
                callTimerEl.textContent = '00:00';
                startTimeEl.textContent = '--:--';

                if (localEnd) {
                    sendSignal('end', {});
                }

                setTimeout(() => {
                    setStatus('已连接');
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
            btnMic.addEventListener('click', () => {
                if (!localStream) return;
                isMuted = !isMuted;
                localStream.getAudioTracks().forEach(t => t.enabled = !isMuted);
                btnMic.classList.toggle('muted', isMuted);
                btnMic.querySelector('i').className = isMuted ? 'fas fa-microphone-slash' : 'fas fa-microphone';
            });

            btnEnd.addEventListener('click', () => endCall(true));

            // 接听来电
            document.getElementById('btn-accept-call').addEventListener('click', async () => {
                if (!pendingOffer) return;
                console.log('接听来电');
                
                setStatus('连接中...');
                await createPeerConnection();
                await pc.setRemoteDescription(new RTCSessionDescription(pendingOffer));
                
                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);
                await sendSignal('answer', answer);
                
                pendingOffer = null;
                setStatus('通话中');
                startTimer();
                incomingModal.style.display = 'none';
            });

            // 拒绝来电
            document.getElementById('btn-decline-call').addEventListener('click', async () => {
                console.log('拒绝来电');
                pendingOffer = null;
                await sendSignal('end', {});
                incomingModal.style.display = 'none';
                setStatus('已拒绝来电');
                setTimeout(() => setStatus('已连接'), 2000);
            });

            // 设置切换
            document.querySelectorAll('.toggle-switch').forEach(toggle => {
                toggle.addEventListener('click', () => {
                    toggle.classList.toggle('active');
                });
            });

            // 开始轮询
            startPolling();

            // 页面卸载时清理
            window.addEventListener('beforeunload', () => {
                if (pc) {
                    endCall(true);
                }
            });

            // 自动开始通话（如果是发起者）
            setTimeout(() => {
                startCall();
            }, 1000);

        })();
    </script>
</body>
</html>
