<?php
session_start();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>语音通话</title>
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

        .hidden {
            display: none !important;
        }

        .debug-info {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="call-container">
        <div class="avatar">
            <i class="fas fa-user"></i>
        </div>
        
        <div class="user-name">语音通话</div>
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
        
        <div class="debug-info">
            <div>房间ID: <?php echo isset($_GET['roomId']) ? $_GET['roomId'] : '未设置'; ?></div>
            <div>用户ID: <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '未登录'; ?></div>
            <div>通话类型: <?php echo isset($_GET['callType']) ? $_GET['callType'] : 'voice'; ?></div>
        </div>
    </div>

    <!-- 远程音频 -->
    <audio class="remote-audio" id="remote-audio" autoplay></audio>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></script>
    <script>
        // 基于成功例子的简化WebRTC实现
        (function() {
            const btnStart = document.getElementById('btn-start-call');
            const btnEnd = document.getElementById('btn-end-call');
            const btnMute = document.getElementById('btn-mute');
            const statusEl = document.getElementById('call-status');
            const remoteAudio = document.getElementById('remote-audio');
            const callTimerEl = document.getElementById('call-timer');

            let pc = null;
            let localStream = null;
            let isMuted = false;

            const roomId = <?php echo isset($_GET['roomId']) ? (int)$_GET['roomId'] : 0; ?>;
            const userId = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; ?>;

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

                    const response = await fetch(`../../app/controllers/CallSignalController.php?action=push&room_id=${roomId}`, {
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

                setStatus('通话中');
                showElement(btnStart, false);
                showElement(btnEnd, true);
                showElement(btnMute, true);
            }

            function endCall() {
                console.log('结束通话');
                if (pc) { 
                    pc.close(); 
                    pc = null; 
                }
                if (localStream) { 
                    localStream.getTracks().forEach(t => t.stop()); 
                    localStream = null; 
                }
                
                setStatus('通话已结束');
                showElement(btnStart, true);
                showElement(btnEnd, false);
                showElement(btnMute, false);
                showElement(callTimerEl, false);

                setTimeout(() => {
                    setStatus('准备通话');
                }, 2000);
            }

            // 事件监听器
            btnStart.addEventListener('click', startCall);
            btnEnd.addEventListener('click', endCall);
            
            btnMute.addEventListener('click', () => {
                if (!localStream) return;
                isMuted = !isMuted;
                localStream.getAudioTracks().forEach(t => t.enabled = !isMuted);
                btnMute.classList.toggle('muted', isMuted);
                btnMute.querySelector('i').className = isMuted ? 'fas fa-microphone-slash' : 'fas fa-microphone';
            });

            // 页面卸载时清理
            window.addEventListener('beforeunload', () => {
                if (pc) {
                    endCall();
                }
            });

            console.log('语音通话页面已加载');
            console.log('房间ID:', roomId);
            console.log('用户ID:', userId);

        })();
    </script>
</body>
</html>
