/**
 * 音频管理模块
 * 负责音频流获取、控制和设备管理
 */

class AudioManager {
    constructor(config = {}) {
        this.config = {
            echoCancellation: true,
            noiseSuppression: true,
            autoGainControl: true,
            sampleRate: 48000,
            channelCount: 1,
            ...config
        };
        
        this.stream = null;
        this.audioContext = null;
        this.analyser = null;
        this.microphone = null;
        this.isMuted = false;
        this.volume = 1.0;
        this.devices = [];
        this.currentDeviceId = null;
        
        this.eventListeners = new Map();
        
        console.log('[AudioManager] 音频管理模块已初始化');
    }
    
    /**
     * 请求麦克风权限并获取音频流
     */
    async requestMicrophone() {
        try {
            const constraints = {
                audio: {
                    echoCancellation: this.config.echoCancellation,
                    noiseSuppression: this.config.noiseSuppression,
                    autoGainControl: this.config.autoGainControl,
                    sampleRate: this.config.sampleRate,
                    channelCount: this.config.channelCount,
                    deviceId: this.currentDeviceId ? { exact: this.currentDeviceId } : undefined
                }
            };
            
            console.log('[AudioManager] 请求音频约束:', constraints);
            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            
            // 验证音频轨道
            const audioTracks = this.stream.getAudioTracks();
            console.log('[AudioManager] 获取到的音频轨道数量:', audioTracks.length);
            
            audioTracks.forEach((track, index) => {
                console.log(`[AudioManager] 音频轨道 ${index}:`, {
                    id: track.id,
                    kind: track.kind,
                    enabled: track.enabled,
                    muted: track.muted,
                    readyState: track.readyState,
                    label: track.label,
                    settings: track.getSettings()
                });
                
                // 监听轨道状态变化
                track.addEventListener('ended', () => {
                    console.warn(`[AudioManager] 音频轨道 ${index} 已结束`);
                });
                
                track.addEventListener('mute', () => {
                    console.warn(`[AudioManager] 音频轨道 ${index} 被静音`);
                });
                
                track.addEventListener('unmute', () => {
                    console.log(`[AudioManager] 音频轨道 ${index} 取消静音`);
                });
            });
            
            // 设置音频上下文和分析器
            this.setupAudioContext();
            
            // 获取音频设备列表
            await this.getAudioDevices();
            
            this.emit('stream_ready', this.stream);
            console.log('[AudioManager] 麦克风权限已获取');
            
            return this.stream;
            
        } catch (error) {
            console.error('[AudioManager] 获取麦克风权限失败:', error);
            this.handleError('MICROPHONE_ACCESS_DENIED', error);
            throw error;
        }
    }
    
    /**
     * 设置音频上下文
     */
    setupAudioContext() {
        try {
            if (!this.stream) return;
            
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.analyser = this.audioContext.createAnalyser();
            this.microphone = this.audioContext.createMediaStreamSource(this.stream);
            
            this.analyser.fftSize = 256;
            this.microphone.connect(this.analyser);
            
            // 开始音量监控
            this.startVolumeMonitoring();
            
            console.log('[AudioManager] 音频上下文已设置');
            
        } catch (error) {
            console.error('[AudioManager] 设置音频上下文失败:', error);
        }
    }
    
    /**
     * 开始音量监控
     */
    startVolumeMonitoring() {
        if (!this.analyser) return;
        
        const dataArray = new Uint8Array(this.analyser.frequencyBinCount);
        
        const monitor = () => {
            if (this.analyser && !this.isMuted) {
                this.analyser.getByteFrequencyData(dataArray);
                
                // 计算平均音量
                const average = dataArray.reduce((sum, value) => sum + value, 0) / dataArray.length;
                const volume = average / 255;
                
                this.emit('volume_change', volume);
            }
            
            requestAnimationFrame(monitor);
        };
        
        monitor();
    }
    
    /**
     * 获取音频设备列表
     */
    async getAudioDevices() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            this.devices = devices.filter(device => device.kind === 'audioinput');
            
            console.log('[AudioManager] 音频设备列表已获取:', this.devices.length);
            return this.devices;
            
        } catch (error) {
            console.error('[AudioManager] 获取音频设备失败:', error);
            return [];
        }
    }
    
    /**
     * 切换音频设备
     */
    async switchAudioDevice(deviceId) {
        try {
            if (this.currentDeviceId === deviceId) {
                return this.stream;
            }
            
            this.currentDeviceId = deviceId;
            
            // 停止当前流
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
            }
            
            // 获取新流
            const newStream = await this.requestMicrophone();
            
            this.emit('device_changed', { deviceId, stream: newStream });
            console.log('[AudioManager] 音频设备已切换:', deviceId);
            
            return newStream;
            
        } catch (error) {
            console.error('[AudioManager] 切换音频设备失败:', error);
            throw error;
        }
    }
    
    /**
     * 切换静音状态
     */
    async toggleMute() {
        try {
            if (!this.stream) {
                throw new Error('音频流未初始化');
            }
            
            this.isMuted = !this.isMuted;
            
            const audioTracks = this.stream.getAudioTracks();
            audioTracks.forEach(track => {
                track.enabled = !this.isMuted;
            });
            
            this.emit('mute_changed', this.isMuted);
            console.log('[AudioManager] 静音状态已切换:', this.isMuted);
            
            return this.isMuted;
            
        } catch (error) {
            console.error('[AudioManager] 切换静音失败:', error);
            throw error;
        }
    }
    
    /**
     * 设置静音状态
     */
    setMute(muted) {
        if (this.isMuted === muted) return;
        
        this.toggleMute();
    }
    
    /**
     * 设置音量
     */
    setVolume(volume) {
        try {
            this.volume = Math.max(0, Math.min(1, volume));
            
            if (this.audioContext && this.microphone) {
                const gainNode = this.audioContext.createGain();
                gainNode.gain.value = this.volume;
                
                // 重新连接音频节点
                this.microphone.disconnect();
                this.microphone.connect(gainNode);
                gainNode.connect(this.analyser);
            }
            
            this.emit('volume_changed', this.volume);
            console.log('[AudioManager] 音量已设置:', this.volume);
            
        } catch (error) {
            console.error('[AudioManager] 设置音量失败:', error);
        }
    }
    
    /**
     * 获取当前音频流
     */
    getStream() {
        return this.stream;
    }
    
    /**
     * 获取音频状态
     */
    getAudioStatus() {
        return {
            isMuted: this.isMuted,
            volume: this.volume,
            hasStream: !!this.stream,
            deviceCount: this.devices.length,
            currentDeviceId: this.currentDeviceId,
            isPlaying: this.stream ? this.stream.getAudioTracks().some(track => track.enabled) : false
        };
    }
    
    /**
     * 测试音频设备
     */
    async testAudioDevice(deviceId) {
        try {
            const testStream = await navigator.mediaDevices.getUserMedia({
                audio: { deviceId: { exact: deviceId } }
            });
            
            // 播放测试音
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const source = audioContext.createMediaStreamSource(testStream);
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(440, audioContext.currentTime);
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            
            oscillator.start();
            
            // 1秒后停止测试
            setTimeout(() => {
                oscillator.stop();
                testStream.getTracks().forEach(track => track.stop());
                audioContext.close();
            }, 1000);
            
            console.log('[AudioManager] 音频设备测试完成:', deviceId);
            
        } catch (error) {
            console.error('[AudioManager] 音频设备测试失败:', error);
            throw error;
        }
    }
    
    /**
     * 停止音频流
     */
    stop() {
        try {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            
            if (this.audioContext) {
                this.audioContext.close();
                this.audioContext = null;
            }
            
            this.analyser = null;
            this.microphone = null;
            this.isMuted = false;
            
            this.emit('stream_stopped');
            console.log('[AudioManager] 音频流已停止');
            
        } catch (error) {
            console.error('[AudioManager] 停止音频流失败:', error);
        }
    }
    
    /**
     * 处理错误
     */
    handleError(errorType, error) {
        console.error(`[AudioManager] 错误 [${errorType}]:`, error);
        this.emit('error', { type: errorType, error });
    }
    
    /**
     * 事件系统
     */
    on(event, callback) {
        if (!this.eventListeners.has(event)) {
            this.eventListeners.set(event, []);
        }
        this.eventListeners.get(event).push(callback);
    }
    
    emit(event, data) {
        if (this.eventListeners.has(event)) {
            this.eventListeners.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error('[AudioManager] 事件回调错误:', error);
                }
            });
        }
    }
    
    /**
     * 获取状态
     */
    getStatus() {
        return {
            ...this.getAudioStatus(),
            config: this.config
        };
    }
}

// 全局导出
window.AudioManager = AudioManager;
