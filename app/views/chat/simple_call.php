<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>语音通话</title>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            color: white;
        }
        .call-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            color: #333;
        }
        .call-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            font-size: 24px;
            margin: 10px;
            background: #4CAF50;
            color: white;
        }
        .call-btn:hover {
            opacity: 0.8;
        }
        .btn-end {
            background: #f44336;
        }
        .status {
            margin: 20px 0;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="call-container">
        <h2>语音通话</h2>
        <div class="status" id="status">准备通话</div>
        
        <div>
            <button class="call-btn" id="btn-start" onclick="startCall()">📞</button>
            <button class="call-btn btn-end" id="btn-end" onclick="endCall()" style="display:none;">📵</button>
        </div>
        
        <div id="info" style="margin-top: 20px; font-size: 14px; color: #666;">
            <div>房间ID: <span id="roomId"></span></div>
            <div>通话类型: <span id="callType"></span></div>
        </div>
    </div>

    <audio id="remote-audio" autoplay style="display: none;"></audio>

    <script>
        let pc = null;
        let localStream = null;

        // 获取URL参数
        const urlParams = new URLSearchParams(window.location.search);
        const roomId = urlParams.get('roomId') || '4';
        const callType = urlParams.get('callType') || 'voice';
        
        document.getElementById('roomId').textContent = roomId;
        document.getElementById('callType').textContent = callType;

        function setStatus(text) {
            document.getElementById('status').textContent = text;
        }

        async function startCall() {
            try {
                setStatus('获取麦克风权限...');
                
                // 获取音频流
                localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                setStatus('创建连接...');
                
                // 创建WebRTC连接
                pc = new RTCPeerConnection({
                    iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
                });

                // 添加音频轨道
                localStream.getTracks().forEach(track => {
                    pc.addTrack(track, localStream);
                });

                // 处理远程流
                pc.ontrack = (e) => {
                    const remoteAudio = document.getElementById('remote-audio');
                    remoteAudio.srcObject = e.streams[0];
                    remoteAudio.style.display = 'block';
                    setStatus('通话中');
                };

                // 处理ICE候选
                pc.onicecandidate = (e) => {
                    if (e.candidate) {
                        console.log('ICE候选:', e.candidate);
                    }
                };

                // 创建offer
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                
                setStatus('通话中');
                document.getElementById('btn-start').style.display = 'none';
                document.getElementById('btn-end').style.display = 'inline-block';
                
                console.log('通话已开始');
                
            } catch (error) {
                console.error('启动通话失败:', error);
                setStatus('启动通话失败: ' + error.message);
            }
        }

        function endCall() {
            if (pc) {
                pc.close();
                pc = null;
            }
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }
            
            setStatus('通话已结束');
            document.getElementById('btn-start').style.display = 'inline-block';
            document.getElementById('btn-end').style.display = 'none';
            document.getElementById('remote-audio').style.display = 'none';
            
            setTimeout(() => {
                setStatus('准备通话');
            }, 2000);
        }

        // 页面加载完成
        console.log('语音通话页面已加载');
        console.log('房间ID:', roomId);
        console.log('通话类型:', callType);
    </script>
</body>
</html>
