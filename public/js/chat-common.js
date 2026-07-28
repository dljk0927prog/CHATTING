/**
 * 聊天页面通用功能
 * 包含文件上传、语音录制、消息发送等共享功能
 */

function chatT(key, params) {
    let str = (window.chatI18n && window.chatI18n[key]) || key;
    if (params) {
        Object.keys(params).forEach(function(k) {
            str = str.split('{' + k + '}').join(params[k]);
        });
    }
    return str;
}

function getMessageSignature(message) {
    return JSON.stringify({
        content: message.content || '',
        is_recalled: message.is_recalled ? 1 : 0,
        is_pinned: message.is_pinned ? 1 : 0,
        file_path: message.file_path || '',
        message_type: message.message_type || 'text'
    });
}

function keepBubbleVisible(bubbleElement) {
    if (bubbleElement) bubbleElement.classList.add('show');
}

function hideBubbleOnLeave(bubbleElement) {
    setTimeout(function() {
        if (bubbleElement && !bubbleElement.matches(':hover')) {
            bubbleElement.classList.remove('show');
        }
    }, 300);
}

function buildBubbleButton(action, icon, label, extraClass) {
    extraClass = extraClass || '';
    const safeLabel = String(label).replace(/"/g, '&quot;');
    return `<button type="button" class="bubble-btn${extraClass}" onclick="${action}" aria-label="${safeLabel}">
        <span class="bubble-icon" aria-hidden="true">${icon}</span>
        <span class="bubble-tooltip">${safeLabel}</span>
    </button>`;
}

function attachMessageBubbleBar(messageElement, messageData, currentUserId) {
    const contentDiv = messageElement.querySelector('.message-content');
    if (!contentDiv || !messageData || !messageData.id) return;

    const existing = contentDiv.querySelector('.message-bubble-bar');
    if (existing) existing.remove();

    const isOwnMessage = Number(messageData.sender_id) === Number(currentUserId);
    const createdAt = new Date(messageData.created_at).getTime();
    const messageAge = isNaN(createdAt) ? 9999 : (Date.now() - createdAt) / 1000;
    const canRecall = isOwnMessage && messageAge <= 120;
    const isTextOnly = messageData.message_type === 'text' && !messageData.file_path;
    const isPinned = !!(messageData.is_pinned == 1 || messageData.is_pinned === true);
    const escContent = String(messageData.content || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');

    const bubbleBar = document.createElement('div');
    bubbleBar.className = 'message-bubble-bar';
    bubbleBar.id = 'bubble-' + messageData.id;

    let html = '';

    if (isOwnMessage) {
        const recallAction = messageData.message_type === 'text' && canRecall ? 'recallMessage' : 'deleteMessage';
        const recallLabel = messageData.message_type === 'text' && canRecall ? chatT('message_recall') : chatT('message_delete');
        const recallIcon = messageData.message_type === 'text' && canRecall ? '↩️' : '🗑️';
        html += buildBubbleButton(`${recallAction}(${messageData.id})`, recallIcon, recallLabel);
        if (isTextOnly) {
            html += buildBubbleButton(`editMessage(${messageData.id}, '${escContent}')`, '✏️', chatT('message_edit'));
        }
    }

    const pinLabel = isPinned ? chatT('message_unpin') : chatT('message_pin');
    html += buildBubbleButton(`toggleFavorite(${messageData.id})`, '⭐', chatT('message_favorite_short'));
    html += buildBubbleButton(`togglePin(${messageData.id})`, '📌', pinLabel, isPinned ? ' pinned' : '');
    html += buildBubbleButton(`quoteMessage(${messageData.id})`, '💬', chatT('quote_label'));
    html += buildBubbleButton(`forwardMessage(${messageData.id})`, '📤', chatT('message_share'));

    bubbleBar.innerHTML = html;
    bubbleBar.addEventListener('mouseenter', function() { keepBubbleVisible(this); });
    bubbleBar.addEventListener('mouseleave', function() { hideBubbleOnLeave(this); });
    contentDiv.appendChild(bubbleBar);
}

function stampExistingMessageSignatures(roomId) {
    if (!roomId) return;
    fetch('/Chat_System/chat/getRoomMessages?room_id=' + roomId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data.success || !Array.isArray(data.messages)) return;
            const sigMap = {};
            data.messages.forEach(function(message) {
                sigMap[String(message.id)] = getMessageSignature(message);
            });
            document.querySelectorAll('#messages-container .message[data-message-id]').forEach(function(el) {
                const id = el.getAttribute('data-message-id');
                if (id && sigMap[id] && !el.getAttribute('data-msg-sig')) {
                    el.setAttribute('data-msg-sig', sigMap[id]);
                }
            });
        })
        .catch(function(err) { console.error('stampExistingMessageSignatures failed:', err); });
}

function syncRoomMessages(roomId, createMessageElementFn, options) {
    options = options || {};
    const container = document.getElementById('messages-container');
    if (!container || typeof createMessageElementFn !== 'function') return;

    const scrollTop = container.scrollTop;
    const scrollHeight = container.scrollHeight;
    const isAtBottom = scrollTop + container.clientHeight >= scrollHeight - 10;

    const existingMap = new Map();
    container.querySelectorAll('[data-message-id]').forEach(function(el) {
        existingMap.set(el.getAttribute('data-message-id'), el);
    });

    fetch('/Chat_System/chat/getRoomMessages?room_id=' + roomId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data.success || !Array.isArray(data.messages)) return;

            let changed = false;
            data.messages.forEach(function(message) {
                const id = String(message.id);
                const signature = getMessageSignature(message);
                const existing = existingMap.get(id);

                if (existing) {
                    const prevSig = existing.getAttribute('data-msg-sig');
                    if (!prevSig) {
                        existing.setAttribute('data-msg-sig', signature);
                        return;
                    }
                    if (prevSig !== signature) {
                        const newEl = createMessageElementFn(message);
                        if (newEl) {
                            newEl.setAttribute('data-msg-sig', signature);
                        if (typeof addMessageEventListeners === 'function') {
                            addMessageEventListeners(newEl);
                        }
                        existing.replaceWith(newEl);
                            changed = true;
                        }
                    }
                } else {
                    const newEl = createMessageElementFn(message);
                    if (newEl) {
                        newEl.setAttribute('data-msg-sig', signature);
                        if (typeof addMessageEventListeners === 'function') {
                            addMessageEventListeners(newEl);
                        }
                        container.appendChild(newEl);
                        changed = true;
                    }
                }
            });

            if (changed) {
                enhanceMediaElements(container);
                if (typeof window.addQuoteClickHandlers === 'function') {
                    window.addQuoteClickHandlers();
                }
                if (isAtBottom || options.forceScroll) {
                    setTimeout(function() {
                        if (typeof scrollToBottom === 'function') scrollToBottom();
                    }, 100);
                }
            }
        })
        .catch(function(error) {
            console.error('syncRoomMessages failed:', error);
        });
}

// 文件上传相关变量
let selectedFiles = [];
let currentFileType = 'image';
const maxFiles = 10;

// 消息气泡相关变量
let messageHoverTimeout = null;

// 引用消息相关变量
let quotedMessageId = null;

// 语音录制相关变量
let mediaRecorder = null;
let audioChunks = [];
let isRecording = false;
let recordingTimer = null;
let recordingDuration = 0;
let maxRecordingDuration = 60; // 最大录音时长60秒
let recordedAudioBlob = null;

/**
 * 文件上传功能
 */
function showFileUploadModal() {
    console.log('showFileUploadModal 被调用 - 新版本');
    // 只显示文件类型选择卡片，不直接触发文件选择
    const fileTypeCards = document.getElementById('fileTypeCards');
    if (fileTypeCards) {
        console.log('找到文件类型选择卡片，切换显示状态');
        fileTypeCards.classList.toggle('hidden');
    } else {
        console.log('未找到文件类型选择卡片元素');
    }
}

function showFileTypeCards() {
    const fileTypeCards = document.getElementById('fileTypeCards');
    if (fileTypeCards) {
        fileTypeCards.classList.toggle('hidden');
    }
}

function hideFileTypeCards() {
    document.getElementById('fileTypeCards').classList.add('hidden');
}

function selectFileType(type) {
    currentFileType = type;
    hideFileTypeCards();
    
    const fileInput = document.getElementById('fileInput');
    switch(type) {
        case 'image':
            fileInput.accept = 'image/*';
            break;
        case 'video':
            fileInput.accept = 'video/*';
            break;
        case 'file':
            fileInput.accept = '*';
            break;
    }
    
    // 延迟触发文件选择，确保菜单先隐藏
    setTimeout(() => {
        fileInput.click();
    }, 100);
}

function handleFileSelect(input) {
    const files = Array.from(input.files);
    if (files.length === 0) return;
    
    // 检查文件数量限制
    if (selectedFiles.length + files.length > maxFiles) {
        alert(chatT('max_files_alert', { max: maxFiles }));
        return;
    }
    
    // 添加新文件到已选择的文件列表
    files.forEach(file => {
        if (selectedFiles.length < maxFiles) {
            selectedFiles.push(file);
        }
    });
    
    // 显示文件预览
    showFilePreview();
}

function showFilePreview() {
    const previewArea = document.getElementById('filePreviewArea');
    const previewContent = document.getElementById('previewContent');
    const previewInfo = document.getElementById('previewInfo');
    const addMoreBtn = document.querySelector('.add-more-btn');
    
    previewArea.classList.remove('hidden');
    
    if (selectedFiles.length === 0) {
        previewArea.classList.add('hidden');
        return;
    }
    
    // 显示文件数量信息
    const totalSize = selectedFiles.reduce((sum, file) => sum + file.size, 0);
    const totalSizeMB = (totalSize / 1024 / 1024).toFixed(2);
    previewInfo.innerHTML = `
        <div class="file-count">${chatT('files_selected', { count: selectedFiles.length })}</div>
        <div class="file-size">${chatT('total_size_mb', { size: totalSizeMB })}</div>
    `;
    
    // 显示文件预览网格
    previewContent.innerHTML = '';
    selectedFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-preview-item';
        fileItem.innerHTML = `
            <div class="file-item-remove" onclick="removeFile(${index})">×</div>
            <div class="file-item-content"></div>
        `;
        
        const contentDiv = fileItem.querySelector('.file-item-content');
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                contentDiv.innerHTML = `<img src="${e.target.result}" alt="${chatT('preview_image_alt')}" class="preview-thumbnail">`;
            };
            reader.readAsDataURL(file);
        } else if (file.type.startsWith('video/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                contentDiv.innerHTML = `
                    <video class="preview-thumbnail">
                        <source src="${e.target.result}" type="${file.type}">
                    </video>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            contentDiv.innerHTML = `
                <div class="file-icon">📄</div>
                <div class="file-name-small">${file.name}</div>
            `;
        }
        
        previewContent.appendChild(fileItem);
    });
    
    // 显示或隐藏添加更多按钮
    if (selectedFiles.length < maxFiles) {
        addMoreBtn.style.display = 'flex';
    } else {
        addMoreBtn.style.display = 'none';
    }
}

function removeFilePreview() {
    selectedFiles = [];
    const previewArea = document.getElementById('filePreviewArea');
    const previewContent = document.getElementById('previewContent');
    const previewInfo = document.getElementById('previewInfo');
    if (previewContent) previewContent.innerHTML = '';
    if (previewInfo) previewInfo.innerHTML = '';
    if (previewArea) previewArea.classList.add('hidden');
    const fileInput = document.getElementById('fileInput');
    if (fileInput) fileInput.value = '';
}
window.removeFilePreview = removeFilePreview;

function removeFile(index) {
    selectedFiles.splice(index, 1);
    showFilePreview();
}

function addMoreFiles() {
    const fileInput = document.getElementById('fileInput');
    fileInput.click();
}

/**
 * 语音录制功能
 */
function startVoiceRecording(event) {
    event.preventDefault();
    
    if (isRecording) {
        return;
    }
    
    // 检查浏览器支持
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('您的浏览器不支持语音录制功能');
        return;
    }
    
    // 请求麦克风权限
    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];
            recordingDuration = 0;
            
            mediaRecorder.ondataavailable = event => {
                audioChunks.push(event.data);
            };
            
            mediaRecorder.onstop = () => {
                recordedAudioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                showVoicePreview(recordedAudioBlob);
                
                // 停止所有音频轨道
                stream.getTracks().forEach(track => track.stop());
            };
            
            mediaRecorder.start();
            isRecording = true;
            
            // 更新按钮状态
            const voiceBtn = document.getElementById('voiceRecordBtn');
            voiceBtn.classList.add('recording');
            voiceBtn.textContent = '🔴';
            
            // 开始计时
            recordingTimer = setInterval(() => {
                recordingDuration++;
                if (recordingDuration >= maxRecordingDuration) {
                    stopVoiceRecording(event);
                }
            }, 1000);
            
            showNotification('开始录音...', 'info');
        })
        .catch(error => {
            console.error('无法访问麦克风:', error);
            alert('无法访问麦克风，请检查权限设置');
        });
}

function stopVoiceRecording(event) {
    event.preventDefault();
    
    if (!isRecording || !mediaRecorder) {
        return;
    }
    
    mediaRecorder.stop();
    isRecording = false;
    
    // 清除计时器
    if (recordingTimer) {
        clearInterval(recordingTimer);
        recordingTimer = null;
    }
    
    // 更新按钮状态
    const voiceBtn = document.getElementById('voiceRecordBtn');
    voiceBtn.classList.remove('recording');
    voiceBtn.textContent = '🎤';
    
    showNotification('录音结束', 'success');
}

function showVoicePreview(audioBlob) {
    const previewArea = document.getElementById('voicePreviewArea');
    const previewPlayer = document.getElementById('voicePreviewPlayer');
    const durationText = document.getElementById('voiceDurationText');
    const sizeText = document.getElementById('voiceSizeText');
    
    // 创建音频URL
    const audioUrl = URL.createObjectURL(audioBlob);
    previewPlayer.src = audioUrl;
    
    // 显示预览区域
    previewArea.classList.remove('hidden');
    
    // 更新时长和大小信息
    const duration = Math.floor(recordingDuration);
    const minutes = Math.floor(duration / 60);
    const seconds = duration % 60;
    durationText.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    const sizeKB = Math.round(audioBlob.size / 1024);
    sizeText.textContent = `${sizeKB} KB`;
}

function removeVoicePreview() {
    recordedAudioBlob = null;
    document.getElementById('voicePreviewArea').classList.add('hidden');
}

/**
 * 消息发送功能
 */
function sendTextMessage(content, roomId) {
    // 构建请求体
    let body = `room_id=${roomId}&content=${encodeURIComponent(content)}`;
    if (quotedMessageId) {
        body += `&quoted_message_id=${quotedMessageId}`;
    }
    
    fetch('/Chat_System/chat/sendMessage', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: body
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('服务器返回的不是有效的JSON:', text);
                throw new Error('服务器返回了无效的响应格式');
            }
        });
    })
    .then(data => {
        if (data.success) {
            // 发送成功后清空输入框
            document.getElementById('message-input').value = '';
            
            // 清除引用状态
            if (quotedMessageId) {
                clearQuote();
            }
            
            // 优先使用服务器返回的消息立即追加到列表（与群组一致）
            if (data.message && typeof addNewMessageToChat === 'function') {
                addNewMessageToChat(data.message);
                if (typeof scrollToBottom === 'function') setTimeout(scrollToBottom, 100);
            }
            if (typeof refreshMessagesArea === 'function') {
                refreshMessagesArea(true);
            } else if (typeof refreshMessages === 'function') {
                refreshMessages();
            }
            if (typeof refreshSidebar === 'function') {
                refreshSidebar();
            }
        } else {
            showNotification(chatT('send_failed') + ': ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('发送消息失败:', error);
        showNotification(chatT('send_failed') + ': ' + error.message, 'error');
    });
}

function sendFileWithMessage(content, roomId) {
    const formData = new FormData();
    
    // 添加所有文件
    selectedFiles.forEach((file, index) => {
        formData.append(`files[]`, file);
    });
    
    formData.append('room_id', roomId);
    formData.append('file_type', currentFileType);
    formData.append('file_count', selectedFiles.length);
    
    fetch('/Chat_System/chat/sendMultipleFiles', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('服务器返回的不是有效的JSON:', text);
                throw new Error('服务器返回了无效的响应格式');
            }
        });
    })
    .then(data => {
        if (data.success) {
            showNotification(chatT('file_send_success'), 'success');
            removeFilePreview();
            document.getElementById('message-input').value = '';
            if (data.message && typeof addNewMessageToChat === 'function') {
                addNewMessageToChat(data.message);
                if (typeof scrollToBottom === 'function') setTimeout(scrollToBottom, 100);
            }
            if (typeof refreshMessagesArea === 'function') {
                refreshMessagesArea(true);
            } else if (typeof refreshMessages === 'function') {
                refreshMessages();
            }
            if (typeof refreshSidebar === 'function') {
                refreshSidebar();
            }
        } else {
            showNotification(chatT('file_send_failed') + ': ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('文件发送失败:', error);
        showNotification(chatT('file_send_failed') + ': ' + error.message, 'error');
    });
}

function sendVoiceMessage(audioBlob, roomId) {
    const formData = new FormData();
    
    // 创建文件对象
    const audioFile = new File([audioBlob], 'voice_message.webm', { type: 'audio/webm' });
    formData.append('voice_file', audioFile);
    formData.append('room_id', roomId);
    
    // 发送语音消息
    fetch('/Chat_System/chat/sendVoiceMessage', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('服务器返回的不是有效的JSON:', text);
                throw new Error('服务器返回了无效的响应格式');
            }
        });
    })
    .then(data => {
        if (data.success) {
            showNotification(chatT('voice_send_success'), 'success');
            removeVoicePreview();
            document.getElementById('message-input').value = '';
            if (data.message && typeof addNewMessageToChat === 'function') {
                addNewMessageToChat(data.message);
                if (typeof scrollToBottom === 'function') setTimeout(scrollToBottom, 100);
            }
            if (typeof refreshMessagesArea === 'function') {
                refreshMessagesArea(true);
            } else if (typeof refreshMessages === 'function') {
                refreshMessages();
            }
            if (typeof refreshSidebar === 'function') {
                refreshSidebar();
            }
        } else {
            showNotification(chatT('voice_send_failed') + ': ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('语音消息发送失败:', error);
        showNotification(chatT('voice_send_failed') + ': ' + error.message, 'error');
    });
}

/**
 * 通知功能
 */
function showNotification(message, type) {
    const notification = document.createElement('div');
    let backgroundColor = '#00bfff'; // 默认蓝色
    
    switch(type) {
        case 'error':
            backgroundColor = '#ff4757';
            break;
        case 'success':
            backgroundColor = '#2ed573';
            break;
        case 'info':
            backgroundColor = '#00bfff';
            break;
        case 'warning':
            backgroundColor = '#ffa502';
            break;
    }
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${backgroundColor};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        z-index: 10000;
        font-family: 'Orbitron', sans-serif;
        font-weight: 600;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        max-width: 300px;
        word-wrap: break-word;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

/**
 * 滚动到底部功能
 */
function scrollToBottom() {
    const messagesContainer = document.getElementById('messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

const MEDIA_VIDEO_EXTENSIONS = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];

function getMediaExtension(url) {
    if (!url) return '';
    try {
        const path = new URL(url, window.location.origin).pathname;
        return path.split('.').pop().toLowerCase();
    } catch (e) {
        return url.split('.').pop().split('?')[0].toLowerCase();
    }
}

function isVideoMediaUrl(url) {
    return MEDIA_VIDEO_EXTENSIONS.includes(getMediaExtension(url));
}

window.showMediaPreview = function(url, isVideo) {
    const modal = document.getElementById('imagePreviewModal');
    if (!modal || !url) return;

    const previewImage = document.getElementById('previewImage');
    const previewVideo = document.getElementById('previewVideo');
    const thumbnails = document.getElementById('previewThumbnails');
    if (thumbnails) thumbnails.innerHTML = '';

    if (isVideo) {
        if (previewImage) previewImage.style.display = 'none';
        if (previewVideo) {
            previewVideo.style.display = 'block';
            previewVideo.src = url;
            previewVideo.load();
        }
    } else {
        if (previewVideo) {
            previewVideo.pause();
            previewVideo.removeAttribute('src');
            previewVideo.style.display = 'none';
        }
        if (previewImage) {
            previewImage.style.display = 'block';
            previewImage.src = url;
        }
    }

    modal.classList.remove('hidden');
};

window.showMediaGallery = function(items, startIndex) {
    if (!items || !items.length) return;
    startIndex = Math.max(0, Math.min(startIndex || 0, items.length - 1));

    const thumbnails = document.getElementById('previewThumbnails');
    if (thumbnails) {
        thumbnails.innerHTML = '';
        items.forEach(function(item, index) {
            const thumb = document.createElement('div');
            thumb.className = 'thumbnail-item' + (index === startIndex ? ' active' : '');
            thumb.onclick = function() {
                window.showMediaGallery(items, index);
            };
            if (item.isVideo) {
                thumb.innerHTML = '<video class="thumbnail-media" muted><source src="' + item.url + '"></video><div class="thumbnail-play-icon">▶</div>';
            } else {
                thumb.innerHTML = '<img src="' + item.url + '" alt="" class="thumbnail-media">';
            }
            thumbnails.appendChild(thumb);
        });
    }

    const current = items[startIndex];
    window.showMediaPreview(current.url, current.isVideo);
};

window.hideImagePreview = function() {
    const modal = document.getElementById('imagePreviewModal');
    if (!modal) return;
    modal.classList.add('hidden');
    const previewVideo = document.getElementById('previewVideo');
    if (previewVideo) {
        previewVideo.pause();
        previewVideo.removeAttribute('src');
    }
};

function openCollagePreview(collageEl, startUrl) {
    const items = [];
    collageEl.querySelectorAll('img.collage-thumbnail, video.collage-thumbnail').forEach(function(el) {
        let url = '';
        if (el.tagName === 'IMG') {
            url = el.src;
        } else {
            const source = el.querySelector('source');
            url = source ? source.src : el.currentSrc;
        }
        if (url) {
            items.push({ url: url, isVideo: isVideoMediaUrl(url) });
        }
    });
    const idx = items.findIndex(function(i) { return i.url === startUrl; });
    window.showMediaGallery(items, idx >= 0 ? idx : 0);
}

function enhanceMediaElements(root) {
    if (!root) return;
    root.querySelectorAll('.message-video:not([data-preview-enhanced])').forEach(function(video) {
        video.setAttribute('data-preview-enhanced', '1');
        const source = video.querySelector('source');
        const url = source ? source.src : video.currentSrc;
        if (!url) return;

        const wrap = document.createElement('div');
        wrap.className = 'media-preview-wrap';
        video.parentNode.insertBefore(wrap, video);
        wrap.appendChild(video);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'media-expand-btn';
        btn.dataset.url = url;
        btn.dataset.type = 'video';
        btn.innerHTML = '⛶';
        btn.title = typeof chatT === 'function' ? chatT('image_preview_title') : '预览';
        wrap.appendChild(btn);
    });
}

function initMediaPreviewClicks() {
    if (window._mediaPreviewInitialized) return;
    window._mediaPreviewInitialized = true;

    document.addEventListener('click', function(e) {
        const expandBtn = e.target.closest('.media-expand-btn');
        if (expandBtn) {
            e.preventDefault();
            e.stopPropagation();
            window.showMediaPreview(expandBtn.dataset.url, expandBtn.dataset.type === 'video');
            return;
        }

        const img = e.target.closest('img.message-image, img.collage-thumbnail');
        if (img) {
            e.preventDefault();
            const collage = img.closest('.image-collage');
            if (collage) {
                openCollagePreview(collage, img.src);
            } else {
                window.showMediaPreview(img.src, false);
            }
            return;
        }

        const collageVideo = e.target.closest('video.collage-thumbnail');
        if (collageVideo) {
            e.preventDefault();
            const collage = collageVideo.closest('.image-collage');
            const source = collageVideo.querySelector('source');
            const url = source ? source.src : collageVideo.currentSrc;
            if (collage && url) {
                openCollagePreview(collage, url);
            } else if (url) {
                window.showMediaPreview(url, true);
            }
        }
    });

    document.addEventListener('click', function(e) {
        const modal = document.getElementById('imagePreviewModal');
        if (!modal || modal.classList.contains('hidden')) return;
        if (e.target === modal) window.hideImagePreview();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') window.hideImagePreview();
    });
}

/**
 * 初始化聊天通用功能
 */
function initChatCommon() {
    initMediaPreviewClicks();
    // 点击外部区域关闭文件类型选择卡片
    document.addEventListener('click', function(e) {
        const fileCards = document.getElementById('fileTypeCards');
        const fileButton = document.querySelector('[onclick="showFileUploadModal()"]');
        
        if (fileCards && !fileCards.classList.contains('hidden')) {
            if (!fileCards.contains(e.target) && !fileButton.contains(e.target)) {
                hideFileTypeCards();
            }
        }
    });
    
    // 页面加载完成后滚动到底部
    document.addEventListener('DOMContentLoaded', function() {
        // 延迟执行以确保所有内容都已加载
        setTimeout(scrollToBottom, 100);
        
        // 初始化消息气泡功能
        initMessageBubbles();
        enhanceMediaElements(document.getElementById('messages-container'));
        enhanceMediaElements(document.getElementById('pinned-messages-container'));
    });
}

/**
 * 消息气泡功能
 */
function initMessageBubbles() {
    console.log('初始化消息气泡功能');
    
    // 为所有消息元素添加鼠标悬停事件监听器
    const messageElements = document.querySelectorAll('[data-message-hover="true"]');
    console.log('找到消息元素数量:', messageElements.length);
    
    messageElements.forEach((messageElement, index) => {
        console.log(`为消息元素 ${index} 添加事件监听器`);
        
        messageElement.addEventListener('mouseenter', function() {
            console.log('鼠标进入消息元素');
            showMessageBubble(this);
        });
        
        messageElement.addEventListener('mouseleave', function() {
            console.log('鼠标离开消息元素');
            hideMessageBubble(this);
        });
    });
}

// 显示消息气泡栏
function showMessageBubble(messageElement) {
    clearTimeout(messageHoverTimeout);
    messageHoverTimeout = setTimeout(function() {
        document.querySelectorAll('.message-bubble-bar.show').forEach(function(bar) {
            if (!messageElement.contains(bar)) {
                bar.classList.remove('show');
            }
        });
        const bubbleBar = messageElement.querySelector('.message-bubble-bar');
        if (bubbleBar) {
            bubbleBar.classList.add('show');
        }
    }, 200);
}

// 隐藏消息气泡栏
function hideMessageBubble(messageElement) {
    clearTimeout(messageHoverTimeout);
    setTimeout(function() {
        const bubbleBar = messageElement.querySelector('.message-bubble-bar');
        if (bubbleBar && !bubbleBar.matches(':hover') && !messageElement.matches(':hover')) {
            bubbleBar.classList.remove('show');
        }
    }, 300);
}

// 移动端长按检测相关变量
let touchStartTime = 0;
let touchStartX = 0;
let touchStartY = 0;
let longPressTimer = null;
let isLongPress = false;
let touchMoved = false;
const LONG_PRESS_DURATION = 500; // 长按持续时间（毫秒）
const TOUCH_MOVE_THRESHOLD = 10; // 触摸移动阈值（像素）

// 处理触摸开始事件
function handleMessageTouchStart(event, messageElement) {
    console.log('触摸开始');
    touchStartTime = Date.now();
    touchStartX = event.touches[0].clientX;
    touchStartY = event.touches[0].clientY;
    touchMoved = false;
    isLongPress = false;
    
    // 清除之前的长按定时器
    if (longPressTimer) {
        clearTimeout(longPressTimer);
    }
    
    // 设置长按定时器
    longPressTimer = setTimeout(() => {
        if (!touchMoved) {
            console.log('检测到长按，显示气泡栏');
            isLongPress = true;
            
            // 添加长按视觉反馈
            messageElement.classList.add('long-press-active');
            
            // 显示气泡栏
            showMessageBubble(messageElement);
            
            // 添加触觉反馈（如果支持）
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        }
    }, LONG_PRESS_DURATION);
    
    // 阻止默认的触摸行为（如选择文本）
    event.preventDefault();
}

// 处理触摸移动事件
function handleMessageTouchMove(event, messageElement) {
    if (!touchStartTime) return;
    
    const touch = event.touches[0];
    const deltaX = Math.abs(touch.clientX - touchStartX);
    const deltaY = Math.abs(touch.clientY - touchStartY);
    
    // 如果移动距离超过阈值，取消长按
    if (deltaX > TOUCH_MOVE_THRESHOLD || deltaY > TOUCH_MOVE_THRESHOLD) {
        touchMoved = true;
        if (longPressTimer) {
            clearTimeout(longPressTimer);
            longPressTimer = null;
        }
    }
}

// 处理触摸结束事件
function handleMessageTouchEnd(event, messageElement) {
    console.log('触摸结束');
    
    // 清除长按定时器
    if (longPressTimer) {
        clearTimeout(longPressTimer);
        longPressTimer = null;
    }
    
    // 移除长按视觉反馈
    messageElement.classList.remove('long-press-active');
    
    // 如果是长按，阻止默认行为
    if (isLongPress) {
        event.preventDefault();
        event.stopPropagation();
        return false;
    }
    
    // 重置状态
    touchStartTime = 0;
    touchMoved = false;
    isLongPress = false;
}

// 阻止右键菜单
function preventContextMenu(event) {
    console.log('阻止右键菜单');
    event.preventDefault();
    event.stopPropagation();
    
    // 显示自定义气泡栏
    const messageElement = event.currentTarget;
    showMessageBubble(messageElement);
    
    return false;
}

/**
 * 消息气泡栏功能函数
 */
// 撤回消息
function recallMessage(messageId) {
    if (confirm(chatT('confirm_recall'))) {
        fetch('/Chat_System/chat/recallMessage', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: `message_id=${messageId}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification(chatT('recall_success'), 'success');
                const roomId = window.currentRoomId;
                if (roomId && typeof createMessageElement === 'function') {
                    syncRoomMessages(roomId, createMessageElement, { forceScroll: false });
                } else if (typeof refreshMessagesArea === 'function') {
                    refreshMessagesArea(true);
                }
            } else {
                showNotification(chatT('recall_failed') + ': ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('撤回消息失败:', error);
            showNotification(chatT('recall_failed_retry'), 'error');
        });
    }
}

// 删除消息
function deleteMessage(messageId) {
    if (confirm(chatT('confirm_delete_message'))) {
        fetch('/Chat_System/chat/deleteMessage', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: `message_id=${messageId}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification(chatT('delete_success'), 'success');
                const roomId = window.currentRoomId;
                if (roomId && typeof createMessageElement === 'function') {
                    syncRoomMessages(roomId, createMessageElement, { forceScroll: false });
                } else if (typeof refreshMessagesArea === 'function') {
                    refreshMessagesArea(true);
                }
            } else {
                showNotification(chatT('delete_failed') + ': ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('删除消息失败:', error);
            showNotification(chatT('delete_failed'), 'error');
        });
    }
}

// 修改消息
function editMessage(messageId, currentContent) {
    const newContent = prompt('修改消息内容:', currentContent);
    if (newContent !== null && newContent.trim() !== currentContent) {
        fetch('/Chat_System/chat/editMessage', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: `message_id=${messageId}&content=${encodeURIComponent(newContent.trim())}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification(chatT('message_edit_success'), 'success');
                const roomId = window.currentRoomId;
                if (roomId && typeof createMessageElement === 'function') {
                    syncRoomMessages(roomId, createMessageElement, { forceScroll: false });
                } else if (typeof refreshMessagesArea === 'function') {
                    refreshMessagesArea(true);
                }
            } else {
                showNotification(chatT('message_edit_failed') + ': ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('修改消息失败:', error);
            showNotification(chatT('message_edit_failed_retry'), 'error');
        });
    }
}

// 动态更新置顶消息显示
function updatePinnedMessagesDisplay() {
    // 获取当前房间ID
    const roomId = getCurrentRoomId();
    if (!roomId) {
        console.log('No room ID found for updatePinnedMessagesDisplay');
        return;
    }
    
    console.log('Updating pinned messages display for room:', roomId);
    
    // 获取置顶消息
    fetch(`/Chat_System/chat/getRoomMessages?room_id=${roomId}`)
        .then(response => {
            console.log('Get messages response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Get messages response data:', data);
            
            if (data.success) {
                // 过滤出置顶消息
                const pinnedMessages = data.messages.filter(msg => msg.is_pinned);
                console.log('Pinned messages found:', pinnedMessages.length);
                
                // 查找或创建置顶消息容器
                let pinnedContainer = document.querySelector('.pinned-messages-container');
                if (!pinnedContainer) {
                    // 创建置顶消息容器
                    pinnedContainer = document.createElement('div');
                    pinnedContainer.className = 'pinned-messages-container';
                    
                    // 插入到聊天头部下方
                    const chatArea = document.querySelector('.chat-area');
                    const messagesContainer = document.querySelector('.messages-container');
                    if (chatArea && messagesContainer) {
                        chatArea.insertBefore(pinnedContainer, messagesContainer);
                    }
                }
                
                // 清空现有置顶消息
                pinnedContainer.innerHTML = '';
                
                // 渲染置顶消息
                pinnedMessages.forEach(message => {
                    const messageElement = createPinnedMessageElement(message);
                    pinnedContainer.appendChild(messageElement);
                });
                enhanceMediaElements(pinnedContainer);
                
                // 如果没有置顶消息，隐藏容器
                if (pinnedMessages.length === 0) {
                    pinnedContainer.style.display = 'none';
                    console.log('No pinned messages, hiding container');
                } else {
                    pinnedContainer.style.display = 'block';
                    console.log('Showing pinned messages container');
                }
            } else {
                console.error('Failed to get messages:', data);
                // 如果获取消息失败，至少隐藏置顶消息容器
                const pinnedContainer = document.querySelector('.pinned-messages-container');
                if (pinnedContainer) {
                    pinnedContainer.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('更新置顶消息显示失败:', error);
        });
}

// 创建置顶消息元素
function createPinnedMessageElement(message) {
    const messageElement = document.createElement('div');
    const isOwn = message.sender_id == getCurrentUserId();
    messageElement.className = `pinned-message ${isOwn ? 'own' : ''}`;
    messageElement.setAttribute('data-message-id', message.id);
    messageElement.setAttribute('data-sender-id', message.sender_id);
    messageElement.setAttribute('data-created-at', message.created_at);
    messageElement.setAttribute('data-message-hover', 'true');
    messageElement.setAttribute('onmouseenter', 'showMessageBubble(this)');
    messageElement.setAttribute('onmouseleave', 'hideMessageBubble(this)');
    messageElement.setAttribute('oncontextmenu', 'preventContextMenu(event)');
    messageElement.setAttribute('ontouchstart', 'handleMessageTouchStart(event, this)');
    messageElement.setAttribute('ontouchend', 'handleMessageTouchEnd(event, this)');
    messageElement.setAttribute('ontouchmove', 'handleMessageTouchMove(event, this)');
    
    // 生成头像HTML
    let avatarHtml = '';
    if (message.avatar && message.avatar !== 'default_avatar.png') {
        avatarHtml = `<img src="/Chat_System/public/uploads/avatars/${message.avatar}" alt="${message.username}${chatT('avatar_alt_suffix')}">`;
    } else {
        avatarHtml = message.username.charAt(0).toUpperCase();
    }
    
    // 格式化时间
    const messageTime = new Date(message.created_at).toLocaleTimeString('zh-CN', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
    
    // 处理消息内容
    let messageContent = '';
    if (message.message_type === 'voice' && !message.is_recalled) {
        messageContent = `
            <div class="voice-message">
                <audio controls class="voice-player">
                    <source src="/Chat_System/${message.file_path}" type="audio/webm">
                    ${chatT('audio_not_supported')}
                </audio>
                <div class="voice-duration">${chatT('voice_message')}</div>
            </div>
        `;
    } else if (message.file_path && !message.is_recalled) {
        // 处理文件消息
        const fileData = JSON.parse(message.file_path);
        if (fileData && fileData.urls && fileData.urls.length > 0) {
            const fileUrls = fileData.urls;
            const fileNames = fileData.names || [];
            const fileCount = fileData.count || fileUrls.length;
            
            if (fileCount === 1) {
                const fileUrl = fileUrls[0];
                const fileName = fileNames[0] || '';
                const fileExtension = fileName.split('.').pop().toLowerCase();
                
                if (['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'].includes(fileExtension)) {
                    messageContent = `
                        <div class="file-message video-message">
                            <video controls class="message-video">
                                <source src="${fileUrl}" type="video/${fileExtension}">
                            </video>
                        </div>
                    `;
                } else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension)) {
                    messageContent = `
                        <div class="file-message image-message">
                            <img src="${fileUrl}" alt="${chatT('message_image_alt')}" class="message-image">
                        </div>
                    `;
                } else {
                    messageContent = `
                        <div class="file-message document-message">
                            <div class="document-message">
                                <div class="file-icon">📄</div>
                                <div class="file-details">
                                    <div class="file-name">${fileName}</div>
                                    <div class="file-type">${chatT('file_type_with_ext', { ext: fileExtension.toUpperCase() })}</div>
                                </div>
                                <a href="${fileUrl}" download class="download-btn">${chatT('download')}</a>
                            </div>
                        </div>
                    `;
                }
            } else {
                // 多文件消息
                let collageHtml = '<div class="image-collage">';
                const displayCount = Math.min(fileCount, 4);
                const hasMore = fileCount > 4;
                
                for (let i = 0; i < displayCount; i++) {
                    const fileUrl = fileUrls[i];
                    const fileName = fileNames[i] || '';
                    const fileExtension = fileName.split('.').pop().toLowerCase();
                    const isVideo = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'].includes(fileExtension);
                    
                    if (i === 3 && hasMore) {
                        collageHtml += `
                            <div class="collage-item more-item">
                                <div class="more-overlay">
                                    <div class="more-dots">⋯</div>
                                    <div class="more-text">more</div>
                                </div>
                                ${isVideo ? 
                                    `<video class="collage-thumbnail" muted><source src="${fileUrl}" type="video/${fileExtension}"></video>` :
                                    `<img src="${fileUrl}" alt="图片" class="collage-thumbnail">`
                                }
                            </div>
                        `;
                    } else {
                        collageHtml += `
                            <div class="collage-item">
                                ${isVideo ? 
                                    `<video class="collage-thumbnail" muted><source src="${fileUrl}" type="video/${fileExtension}"></video>` :
                                    `<img src="${fileUrl}" alt="图片" class="collage-thumbnail">`
                                }
                            </div>
                        `;
                    }
                }
                collageHtml += '</div>';
                
                messageContent = `
                    <div class="file-message multiple-files-message">
                        ${collageHtml}
                        <div class="files-info">${chatT('message_files_count', { count: fileCount })}</div>
                    </div>
                `;
            }
        }
    } else if (message.is_recalled) {
        messageContent = `
            <div class="recalled-message">
                <span class="recall-icon">↩️</span>
                <span class="recall-text">${chatT('message_recalled_label')}</span>
            </div>
        `;
    } else {
        messageContent = `<div class="message-text">${message.content.replace(/\n/g, '<br>')}</div>`;
    }
    
    messageElement.innerHTML = `
        <div class="message-avatar">
            ${avatarHtml}
        </div>
        <div class="message-content">
            ${messageContent}
            <div class="message-time">${messageTime}</div>
            <div class="message-bubble-bar">
                <button class="bubble-btn pinned" 
                        onclick="togglePin(${message.id})"
                        title="取消置顶"
                        data-pinned="true">
                    📌
                    <div class="bubble-tooltip">取消置顶</div>
                </button>
            </div>
        </div>
    `;
    
    return messageElement;
}

// 获取当前房间ID
function getCurrentRoomId() {
    // 尝试从URL参数获取
    const urlParams = new URLSearchParams(window.location.search);
    const roomId = urlParams.get('id');
    if (roomId) {
        console.log('Room ID from URL:', roomId);
        return roomId;
    }
    
    // 尝试从全局变量获取（group.php中定义的const roomId）
    if (typeof roomId !== 'undefined') {
        console.log('Room ID from const variable:', roomId);
        return roomId;
    }
    
    // 尝试从window对象获取
    if (typeof window.roomId !== 'undefined') {
        console.log('Room ID from window variable:', window.roomId);
        return window.roomId;
    }
    
    // 尝试从页面中查找房间ID
    const roomIdInput = document.querySelector('input[name="room_id"]');
    if (roomIdInput) {
        console.log('Room ID from input field:', roomIdInput.value);
        return roomIdInput.value;
    }
    
    console.log('No room ID found');
    return null;
}

// 获取当前用户ID
function getCurrentUserId() {
    // 尝试从全局变量获取
    if (typeof window.userId !== 'undefined') return window.userId;
    
    return null;
}

// 切换收藏状态
function toggleFavorite(messageId) {
    fetch('/Chat_System/chat/toggleFavorite', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: `message_id=${messageId}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const favoriteBtn = document.querySelector(`[onclick="toggleFavorite(${messageId})"]`);
            if (favoriteBtn) {
                if (data.favorited) {
                    favoriteBtn.classList.add('favorited');
                    favoriteBtn.setAttribute('data-favorited', 'true');
                    favoriteBtn.title = '取消收藏';
                    favoriteBtn.innerHTML = '⭐<div class="bubble-tooltip">取消收藏</div>';
                    showNotification('消息已添加到收藏', 'success');
                } else {
                    favoriteBtn.classList.remove('favorited');
                    favoriteBtn.setAttribute('data-favorited', 'false');
                    favoriteBtn.title = '添加到收藏';
                    favoriteBtn.innerHTML = '⭐<div class="bubble-tooltip">收藏</div>';
                    showNotification('已取消收藏', 'success');
                }
            } else {
                console.error('找不到收藏按钮元素');
            }
        } else {
            showNotification('操作失败: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('收藏操作失败:', error);
        showNotification('收藏操作失败', 'error');
    });
}

// 切换置顶状态
function togglePin(messageId) {
    fetch('/Chat_System/chat/togglePin', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: `message_id=${messageId}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('TogglePin response:', data);
        
        if (data.success) {
            console.log('TogglePin success, pinned:', data.pinned);
            
            // 尝试多种方式查找按钮
            let pinBtn = document.querySelector(`[onclick="togglePin(${messageId})"]`);
            if (!pinBtn) {
                // 如果在置顶消息区域，尝试其他选择器
                pinBtn = document.querySelector(`button[data-message-id="${messageId}"]`);
            }
            if (!pinBtn) {
                // 查找包含messageId的按钮
                pinBtn = document.querySelector(`button[onclick*="${messageId}"]`);
            }
            
            const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
            
            console.log('Pin button found:', pinBtn);
            console.log('Message element found:', messageElement);
            
            if (pinBtn && messageElement) {
                if (data.pinned) {
                    pinBtn.classList.add('pinned');
                    pinBtn.setAttribute('data-pinned', 'true');
                    pinBtn.title = '取消置顶';
                    pinBtn.innerHTML = '📌<div class="bubble-tooltip">取消置顶</div>';
                    showNotification('消息已置顶', 'success');
                } else {
                    pinBtn.classList.remove('pinned');
                    pinBtn.setAttribute('data-pinned', 'false');
                    pinBtn.title = '置顶消息';
                    pinBtn.innerHTML = '📌<div class="bubble-tooltip">置顶</div>';
                    showNotification('已取消置顶', 'success');
                }
            } else {
                console.error('找不到置顶按钮或消息元素');
                console.error('Message ID:', messageId);
                console.error('Available buttons:', document.querySelectorAll('button[onclick*="togglePin"]'));
            }
            
            // 动态更新置顶消息显示，不刷新页面
            updatePinnedMessagesDisplay();
        } else {
            console.error('TogglePin failed:', data);
            showNotification('操作失败: ' + (data.message || '未知错误'), 'error');
        }
    })
    .catch(error => {
        console.error('置顶操作失败:', error);
        showNotification('置顶操作失败', 'error');
    });
}

// 引用消息
function quoteMessage(messageId) {
    console.log('引用消息:', messageId);
    
    // 获取消息元素
    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
    if (!messageElement) {
        console.error('找不到消息元素:', messageId);
        showNotification('找不到要引用的消息', 'error');
        return;
    }
    
    // 获取消息内容
    let messageContent = '';
    let messageSender = '';
    let messageType = '';
    
    // 尝试获取消息文本内容
    const textElement = messageElement.querySelector('.message-text');
    if (textElement) {
        messageContent = textElement.textContent || textElement.innerText || '';
    } else {
        // 如果没有找到文本元素，尝试获取其他内容
        const contentElement = messageElement.querySelector('.message-content');
        if (contentElement) {
            messageContent = contentElement.textContent || contentElement.innerText || '';
        }
    }
    
    // 获取发送者信息
    const senderElement = messageElement.querySelector('.message-sender') || 
                         messageElement.querySelector('.sender-name') ||
                         messageElement.querySelector('.username');
    if (senderElement) {
        messageSender = senderElement.textContent || senderElement.innerText || '';
    } else {
        // 从数据属性获取
        const senderId = messageElement.getAttribute('data-sender-id');
        const senderName = messageElement.getAttribute('data-sender-name');
        if (senderName) {
            messageSender = senderName;
        } else if (senderId) {
            messageSender = '用户' + senderId;
        } else {
            messageSender = '未知用户';
        }
    }
    
    // 检查消息类型
    const fileMessage = messageElement.querySelector('.file-message');
    const voiceMessage = messageElement.querySelector('.voice-message');
    const recalledMessage = messageElement.querySelector('.recalled-message');
    
    if (recalledMessage) {
        messageType = chatT('message_recalled_label');
        messageContent = chatT('message_recalled_desc');
    } else if (voiceMessage) {
        messageType = '[语音消息]';
        messageContent = '语音消息';
    } else if (fileMessage) {
        const fileInfo = messageElement.querySelector('.files-info');
        if (fileInfo) {
            messageType = `[${fileInfo.textContent}]`;
        } else {
            messageType = '[文件]';
        }
        messageContent = chatT('file_message_text');
    }
    
    // 设置引用的消息ID
    quotedMessageId = messageId;
    
    // 构建引用内容（仅用于显示）
    const quotedContent = `> ${messageSender}: ${messageType}${messageContent}`;
    
    // 将引用内容添加到输入框
    const messageInput = document.getElementById('message-input');
    if (messageInput) {
        // 如果输入框已有内容，在末尾添加引用
        const currentContent = messageInput.value.trim();
        if (currentContent) {
            messageInput.value = currentContent + '\n\n' + quotedContent;
        } else {
            messageInput.value = quotedContent + '\n\n';
        }
        
        // 聚焦到输入框
        messageInput.focus();
        
        // 将光标移动到引用内容之后
        const cursorPosition = messageInput.value.length;
        messageInput.setSelectionRange(cursorPosition, cursorPosition);
        
        showNotification('已引用消息', 'success');
        
        // 显示引用指示器
        showQuoteIndicator(messageSender, messageContent);
    } else {
        console.error('找不到消息输入框');
        showNotification('无法引用消息，找不到输入框', 'error');
    }
}

// 显示引用指示器
function showQuoteIndicator(sender, content) {
    // 查找或创建引用指示器
    let indicator = document.getElementById('quoteIndicator');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'quoteIndicator';
        indicator.className = 'quote-indicator';
        indicator.innerHTML = `
            <div class="quote-indicator-content">
                <span class="quote-indicator-label">引用</span>
                <span class="quote-indicator-text">${sender}: ${content.substring(0, 50)}${content.length > 50 ? '...' : ''}</span>
                <button class="quote-indicator-close" onclick="clearQuote()">×</button>
            </div>
        `;
        
        // 插入到消息输入框上方
        const messageInputContainer = document.querySelector('.message-input-container');
        if (messageInputContainer) {
            messageInputContainer.insertBefore(indicator, messageInputContainer.firstChild);
        }
    } else {
        // 更新现有指示器
        const textElement = indicator.querySelector('.quote-indicator-text');
        if (textElement) {
            textElement.textContent = `${sender}: ${content.substring(0, 50)}${content.length > 50 ? '...' : ''}`;
        }
    }
}

// 清除引用
function clearQuote() {
    quotedMessageId = null;
    const indicator = document.getElementById('quoteIndicator');
    if (indicator) {
        indicator.remove();
    }
    showNotification('已取消引用', 'info');
}

// 转发消息
function forwardMessage(messageId) {
    // 显示转发模态框
    showForwardModal(messageId);
}

// 显示转发模态框
function showForwardModal(messageId) {
    // 使用页面中已存在的模态框
    const modal = document.getElementById('forwardModal');
    if (modal) {
        modal.classList.add('show');
        modal.setAttribute('data-message-id', messageId);
        
        // 加载转发数据
        loadForwardData(messageId);
    } else {
        console.error('转发模态框未找到');
    }
}

// 隐藏转发模态框
function hideForwardModal() {
    const modal = document.getElementById('forwardModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

// 加载转发数据
function loadForwardData(messageId) {
    // 加载消息预览 - 使用页面已有的消息数据
    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
    console.log('loadForwardData - messageId:', messageId);
    console.log('loadForwardData - messageElement:', messageElement);
    
    if (messageElement) {
        // 尝试多种方式获取消息内容
        let messageContent = messageElement.querySelector('.message-text') ||
                           messageElement.querySelector('.message-content') || 
                           messageElement.querySelector('.message-body');
        
        console.log('loadForwardData - messageContent element:', messageContent);
        
        let messageSender = messageElement.querySelector('.message-sender') ||
                           messageElement.querySelector('.sender-name') ||
                           messageElement.querySelector('.username');
        
        console.log('loadForwardData - messageSender element:', messageSender);
        
        // 如果找不到发送者元素，尝试从数据属性中获取
        if (!messageSender) {
            const senderId = messageElement.getAttribute('data-sender-id');
            const senderName = messageElement.getAttribute('data-sender-name');
            console.log('loadForwardData - senderId:', senderId, 'senderName:', senderName);
            if (senderName) {
                // 创建一个临时的发送者元素
                messageSender = { textContent: senderName };
            }
        }
        
        let messageFile = messageElement.querySelector('.message-file') ||
                         messageElement.querySelector('.file-info') ||
                         messageElement.querySelector('.attachment');
        
        const previewContent = document.getElementById('forwardPreviewContent');
        
        if (messageContent) {
            // 获取纯文本内容，避免包含HTML标签
            let textContent = messageContent.textContent || messageContent.innerText || '';
            const senderName = messageSender ? (messageSender.textContent || messageSender.innerText || '未知用户') : '未知用户';
            
            // 检查是否是文件消息
            const fileMessage = messageElement.querySelector('.file-message');
            const voiceMessage = messageElement.querySelector('.voice-message');
            const recalledMessage = messageElement.querySelector('.recalled-message');
            
            let messageType = '';
            if (recalledMessage) {
                messageType = '[撤回消息]';
                textContent = '此消息已被撤回';
            } else if (voiceMessage) {
                messageType = '[语音消息]';
                textContent = '语音消息';
            } else if (fileMessage) {
                const fileInfo = messageElement.querySelector('.files-info');
                if (fileInfo) {
                    messageType = `[${fileInfo.textContent}]`;
                } else {
                    messageType = '[文件]';
                }
                textContent = '文件消息';
            } else if (textContent.trim() === '') {
                // 如果没有文本内容，可能是其他类型的消息
                textContent = '消息内容';
            }
            
            console.log('loadForwardData - textContent:', textContent);
            console.log('loadForwardData - senderName:', senderName);
            console.log('loadForwardData - messageType:', messageType);
            
            previewContent.innerHTML = `
                <div class="message-preview">
                    <strong>${senderName}:</strong> ${messageType}${textContent.substring(0, 100)}${textContent.length > 100 ? '...' : ''}
                </div>
            `;
        } else {
            // 如果找不到特定的消息内容元素，尝试获取整个消息的文本
            const allText = messageElement.textContent || messageElement.innerText || '';
            const lines = allText.split('\n').filter(line => line.trim());
            const messageText = lines.slice(0, 3).join(' ').substring(0, 100);
            
            console.log('loadForwardData - allText fallback:', allText);
            console.log('loadForwardData - messageText fallback:', messageText);
            
            previewContent.innerHTML = `
                <div class="message-preview">
                    <strong>消息:</strong> ${messageText}${messageText.length >= 100 ? '...' : ''}
                </div>
            `;
        }
    } else {
        const previewContent = document.getElementById('forwardPreviewContent');
        previewContent.innerHTML = '<div class="message-preview">消息未找到</div>';
    }
    
    // 加载接收者列表 - 使用页面已有的好友和群组数据
    const recipientsList = document.getElementById('recipientsList');
    recipientsList.innerHTML = '';
    
    // 获取好友列表 - 从页面中获取
    const friends = window.friendsData || [];
    const groups = window.groupsData || [];
    
    console.log('loadForwardData - friends:', friends);
    console.log('loadForwardData - groups:', groups);
    
    // 添加好友 - 过滤掉当前私聊用户
    friends.forEach(friend => {
        // 如果是私聊页面，过滤掉当前聊天用户
        if (window.currentChat && window.currentChat.type === 'private' && friend.id == window.currentChat.id) {
            console.log('过滤掉当前私聊用户:', friend.username);
            return;
        }
        
        const recipientItem = document.createElement('div');
        recipientItem.className = 'recipient-item';
        recipientItem.setAttribute('data-recipient-id', `user_${friend.id}`);
        recipientItem.innerHTML = `
            <div class="recipient-avatar">
                ${friend.avatar ? `<img src="/Chat_System/public/uploads/avatars/${friend.avatar}" alt="${friend.username}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">` : friend.username.charAt(0).toUpperCase()}
            </div>
            <div class="recipient-name">${friend.username}</div>
        `;
        recipientItem.onclick = () => toggleRecipient(recipientItem, `user_${friend.id}`);
        recipientsList.appendChild(recipientItem);
    });
    
    // 添加群组 - 过滤掉当前群组
    groups.forEach(group => {
        // 如果是群组页面，过滤掉当前群组
        if (window.currentChat && window.currentChat.type === 'group' && group.id == window.currentChat.id) {
            console.log('过滤掉当前群组:', group.name);
            return;
        }
        
        const recipientItem = document.createElement('div');
        recipientItem.className = 'recipient-item';
        recipientItem.setAttribute('data-recipient-id', `group_${group.id}`);
        recipientItem.innerHTML = `
            <div class="recipient-avatar">
                ${group.avatar ? `<img src="/Chat_System/public/uploads/avatars/${group.avatar}" alt="${group.name}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">` : group.name.charAt(0).toUpperCase()}
            </div>
            <div class="recipient-name">${group.name}</div>
        `;
        recipientItem.onclick = () => toggleRecipient(recipientItem, `group_${group.id}`);
        recipientsList.appendChild(recipientItem);
    });
    
    if (friends.length === 0 && groups.length === 0) {
        recipientsList.innerHTML = '<div class="no-recipients">暂无可转发的联系人</div>';
    }
}

// 切换接收者选择
function toggleRecipient(element, recipientId) {
    element.classList.toggle('selected');
    updateSendButton();
}

// 更新发送按钮状态
function updateSendButton() {
    const selectedRecipients = document.querySelectorAll('.recipient-item.selected');
    const sendBtn = document.getElementById('forwardSendBtn');
    sendBtn.disabled = selectedRecipients.length === 0;
}

// 发送转发消息
function sendForwardMessage() {
    const selectedRecipients = Array.from(document.querySelectorAll('.recipient-item.selected'))
        .map(item => item.getAttribute('data-recipient-id'));
    
    if (selectedRecipients.length === 0) {
        showNotification('请选择至少一个接收者', 'warning');
        return;
    }
    
    const messageId = document.getElementById('forwardModal').getAttribute('data-message-id');
    
    fetch('/Chat_System/chat/forwardMessage', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: `message_id=${messageId}&recipients=${JSON.stringify(selectedRecipients)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('消息转发成功', 'success');
            hideForwardModal();
        } else {
            showNotification('转发失败: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('转发消息失败:', error);
        showNotification(chatT('send_failed_retry'), 'error');
    });
}

// 将函数暴露到全局作用域
window.keepBubbleVisible = keepBubbleVisible;
window.hideBubbleOnLeave = hideBubbleOnLeave;
window.attachMessageBubbleBar = attachMessageBubbleBar;
window.stampExistingMessageSignatures = stampExistingMessageSignatures;
window.showMessageBubble = showMessageBubble;
window.hideMessageBubble = hideMessageBubble;
window.preventContextMenu = preventContextMenu;
window.handleMessageTouchStart = handleMessageTouchStart;
window.handleMessageTouchEnd = handleMessageTouchEnd;
window.handleMessageTouchMove = handleMessageTouchMove;
window.quoteMessage = quoteMessage;
window.clearQuote = clearQuote;
window.forwardMessage = forwardMessage;
window.hideForwardModal = hideForwardModal;
window.sendForwardMessage = sendForwardMessage;

// 确保函数立即可用
console.log('chat-common.js 加载完成，quoteMessage 函数状态:', typeof window.quoteMessage);

// 添加其他可能缺失的函数
if (typeof window.toggleSidebar === 'undefined') {
    window.toggleSidebar = function() {
        console.log('toggleSidebar called - function not implemented');
    };
}

if (typeof window.startGroupVoiceCall === 'undefined') {
    window.startGroupVoiceCall = function() {
        console.log('startGroupVoiceCall called - function not implemented');
    };
}

if (typeof window.startGroupVideoCall === 'undefined') {
    window.startGroupVideoCall = function() {
        console.log('startGroupVideoCall called - function not implemented');
    };
}

if (typeof window.selectFileType === 'undefined') {
    window.selectFileType = function(type) {
        console.log('selectFileType called with type:', type);
    };
}

if (typeof window.addMoreFiles === 'undefined') {
    window.addMoreFiles = function() {
        console.log('addMoreFiles called');
    };
}

if (typeof window.showFileUploadModal === 'undefined') {
    window.showFileUploadModal = function() {
        console.log('showFileUploadModal called');
    };
}

if (typeof window.startVoiceRecording === 'undefined') {
    window.startVoiceRecording = function(event) {
        console.log('startVoiceRecording called');
    };
}

if (typeof window.stopVoiceRecording === 'undefined') {
    window.stopVoiceRecording = function(event) {
        console.log('stopVoiceRecording called');
    };
}

if (typeof window.hideEditModal === 'undefined') {
    window.hideEditModal = function() {
        console.log('hideEditModal called');
    };
}

if (typeof window.saveEditMessage === 'undefined') {
    window.saveEditMessage = function() {
        console.log('saveEditMessage called');
    };
}

