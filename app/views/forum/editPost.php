<?php
// 格式化文件大小函数已移至 Language.php 中
?>
<?php
// 包含语言支持
require_once BASE_PATH . '/lang/Language.php';
$lang = Language::getInstance();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title><?php echo str_replace('{forum_name}', htmlspecialchars($post['forum_name']), __('post_edit_title')); ?></title>
    <link rel="stylesheet" href="/Chat_System/public/css/style.css">
    <style>
        .edit-container {
            height: 100%;
            padding: 20px;
            overflow-y: auto;
            max-width: 100%;
            margin: 0 auto;
            width: 100%;
        }
        
        /* 响应式设计 */
        @media (min-width: 1200px) {
            .edit-container {
                max-width: 1000px;
                padding: 30px;
            }
        }
        
        @media (min-width: 992px) and (max-width: 1199px) {
            .edit-container {
                max-width: 100%;
                padding: 25px;
            }
        }
        
        @media (max-width: 991px) {
            .edit-container {
                padding: 15px;
            }
        }
        
        /* 移动端优化 */
        @media (max-width: 768px) {
            .edit-container {
                padding: 10px;
            }
            
            .edit-header {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .edit-title {
                font-size: 1.3rem;
                line-height: 1.4;
            }
            
            .edit-form {
                gap: 20px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-label {
                font-size: 0.9rem;
                margin-bottom: 8px;
            }
            
            .form-input, .form-textarea {
                min-height: 44px;
                font-size: 16px;
                padding: 12px 15px;
            }
            
            .form-textarea {
                min-height: 200px;
            }
            
            .btn {
                min-height: 48px;
                font-size: 16px;
                padding: 14px 20px;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-group .btn {
                width: 100%;
            }
            
            .file-list {
                gap: 10px;
            }
            
            .file-item {
                padding: 12px;
            }
            
            .file-icon {
                width: 30px;
                height: 30px;
                font-size: 1rem;
            }
            
            .file-info h4 {
                font-size: 0.9rem;
            }
            
            .file-info p {
                font-size: 0.75rem;
            }
        }
        
        @media (max-width: 480px) {
            .edit-container {
                padding: 5px;
            }
            
            .edit-header {
                padding: 15px;
                margin-bottom: 15px;
            }
            
            .edit-title {
                font-size: 1.2rem;
            }
            
            .edit-form {
                gap: 15px;
            }
            
            .form-input, .form-textarea {
                min-height: 42px;
                font-size: 16px;
                padding: 10px 12px;
            }
            
            .form-textarea {
                min-height: 150px;
            }
            
            .btn {
                min-height: 46px;
                font-size: 16px;
                padding: 12px 16px;
            }
            
            .file-item {
                padding: 10px;
            }
            
            .file-icon {
                width: 25px;
                height: 25px;
                font-size: 0.9rem;
            }
            
            .file-info h4 {
                font-size: 0.85rem;
            }
            
            .file-info p {
                font-size: 0.7rem;
            }
        }
        }
        
        @media (max-width: 768px) {
            .edit-container {
                padding: 10px;
            }
        }
        
        .edit-header {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .edit-header {
                padding: 20px;
                margin-bottom: 15px;
                border-radius: 8px;
            }
        }
        
        @media (max-width: 480px) {
            .edit-header {
                padding: 15px;
                margin-bottom: 10px;
            }
        }
        
        .edit-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .edit-title {
                font-size: 1.5rem;
                margin-bottom: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .edit-title {
                font-size: 1.3rem;
                margin-bottom: 12px;
            }
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 1rem;
        }
        
        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }
        
        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            resize: vertical;
            min-height: 200px;
            box-sizing: border-box;
            font-family: inherit;
            line-height: 1.6;
            transition: border-color 0.3s ease;
        }
        
        .form-textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }
        
        @media (max-width: 768px) {
            .form-input, .form-textarea {
                padding: 10px;
                font-size: 16px; /* 防止iOS缩放 */
            }
        }
        
        @media (max-width: 480px) {
            .form-input, .form-textarea {
                padding: 8px;
            }
            
            .form-textarea {
                min-height: 150px;
            }
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
                align-items: stretch;
                margin-top: 20px;
                padding-top: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .form-actions {
                margin-top: 15px;
                padding-top: 12px;
            }
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .btn {
                padding: 12px 20px;
                font-size: 16px;
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .btn {
                padding: 10px 15px;
                font-size: 14px;
            }
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .loading.show {
            display: block;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
            display: none;
        }
        
        .success-message.show {
            display: block;
        }
        
        /* 字符计数 */
        .char-count {
            text-align: right;
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }
        
        .char-count.warning {
            color: #ffc107;
        }
        
        .char-count.danger {
            color: #dc3545;
        }
        
        /* 附件相关样式 */
        .existing-attachments {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .attachment-item {
            position: relative;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .attachment-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .attachment-preview {
            position: relative;
            width: 100%;
            height: 120px;
            overflow: hidden;
        }
        
        .attachment-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: opacity 0.2s ease;
        }
        
        .attachment-thumbnail:hover {
            opacity: 0.9;
        }
        
        .attachment-file {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: #f8f9fa;
        }
        
        .file-icon {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        
        .file-name {
            font-size: 0.8rem;
            text-align: center;
            word-break: break-word;
            padding: 0 10px;
        }
        
        .attachment-info {
            padding: 10px;
            background: #f8f9fa;
        }
        
        .attachment-name {
            font-size: 0.9rem;
            font-weight: 500;
            color: #333;
            margin-bottom: 3px;
            word-break: break-word;
        }
        
        .attachment-size {
            font-size: 0.8rem;
            color: #666;
        }
        
        .remove-attachment-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }
        
        .remove-attachment-btn:hover {
            background: rgba(220, 53, 69, 1);
        }
        
         .file-hint {
             font-size: 0.8rem;
             color: #666;
             margin-top: 5px;
         }
         
         /* 添加附件按钮样式 */
         .add-attachment-item {
             border: 2px dashed #ccc;
             background: #fafafa;
             cursor: pointer;
             transition: all 0.3s ease;
             display: flex;
             align-items: center;
             justify-content: center;
             min-height: 120px;
         }
         
         .add-attachment-item:hover {
             border-color: #667eea;
             background: #f8f9ff;
             transform: translateY(-2px);
             box-shadow: 0 4px 8px rgba(102, 126, 234, 0.15);
         }
         
         .add-attachment-content {
             text-align: center;
             color: #666;
         }
         
         .add-attachment-icon {
             font-size: 2.5rem;
             font-weight: 300;
             margin-bottom: 8px;
             color: #999;
             transition: color 0.3s ease;
         }
         
         .add-attachment-item:hover .add-attachment-icon {
             color: #667eea;
         }
         
         .add-attachment-text {
             font-size: 0.9rem;
             font-weight: 500;
             color: #666;
             transition: color 0.3s ease;
         }
         
         .add-attachment-item:hover .add-attachment-text {
             color: #667eea;
         }
        
        /* 响应式设计 */
         @media (max-width: 768px) {
             .existing-attachments {
                 grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                 gap: 10px;
             }
             
             .attachment-preview {
                 height: 100px;
             }
             
             .add-attachment-item {
                 min-height: 100px;
             }
             
             .add-attachment-icon {
                 font-size: 2rem;
                 margin-bottom: 6px;
             }
             
             .add-attachment-text {
                 font-size: 0.8rem;
             }
         }
         
         @media (max-width: 480px) {
             .existing-attachments {
                 grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                 gap: 8px;
             }
             
             .attachment-preview {
                 height: 80px;
             }
             
             .add-attachment-item {
                 min-height: 80px;
             }
             
             .add-attachment-icon {
                 font-size: 1.8rem;
                 margin-bottom: 4px;
             }
             
             .add-attachment-text {
                 font-size: 0.75rem;
             }
         }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- 引入侧边栏组件 -->
        <?php include __DIR__ . '/../components/navbar.php'; ?>
        
        <!-- 编辑帖子区域 -->
        <div class="chat-area">
            <div class="edit-container">
                <div class="edit-header">
                    <h1 class="edit-title">编辑帖子</h1>
                    
                    <div class="error-message" id="errorMessage"></div>
                    <div class="success-message" id="successMessage"></div>
                    
                    <form id="editPostForm" enctype="multipart/form-data">
                        <input type="hidden" id="postId" value="<?php echo $post['id']; ?>">
                        
                        <div class="form-group">
                            <label for="postTitle" class="form-label">帖子标题</label>
                            <input type="text" id="postTitle" name="title" class="form-input" 
                                   value="<?php echo htmlspecialchars($post['title']); ?>" 
                                   placeholder="请输入帖子标题" required maxlength="200">
                            <div class="char-count" id="titleCount">0/200</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="postContent" class="form-label">帖子内容</label>
                            <textarea id="postContent" name="content" class="form-textarea" 
                                      placeholder="请输入帖子内容" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                            <div class="char-count" id="contentCount">0/5000</div>
                        </div>
                        
                        <!-- 现有附件显示 -->
                        <?php if (!empty($post['media_files'])): ?>
                        <div class="form-group">
                            <label class="form-label">现有附件</label>
                            <div class="existing-attachments">
                                <?php foreach ($post['media_files'] as $media): ?>
                                    <?php 
                                    $filePath = '/Chat_System/public/uploads/files/' . $media['filename'];
                                    $isImage = in_array($media['file_type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                                    $isVideo = in_array($media['file_type'], ['video/mp4', 'video/webm', 'video/quicktime']);
                                    ?>
                                    <div class="attachment-item" data-file-id="<?php echo $media['id']; ?>">
                                        <div class="attachment-preview">
                                            <?php if ($isImage): ?>
                                                <img src="<?php echo $filePath; ?>" 
                                                     alt="<?php echo htmlspecialchars($media['original_name']); ?>"
                                                     onclick="openMediaModal('<?php echo $filePath; ?>', 'image')"
                                                     class="attachment-thumbnail">
                                            <?php elseif ($isVideo): ?>
                                                <video class="attachment-thumbnail" preload="metadata">
                                                    <source src="<?php echo $filePath; ?>" type="<?php echo $media['file_type']; ?>">
                                                </video>
                                            <?php else: ?>
                                                <div class="attachment-file">
                                                    <div class="file-icon">📄</div>
                                                    <div class="file-name"><?php echo htmlspecialchars($media['original_name']); ?></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="attachment-info">
                                            <div class="attachment-name"><?php echo htmlspecialchars($media['original_name']); ?></div>
                                            <div class="attachment-size"><?php echo formatFileSize($media['file_size']); ?></div>
                                        </div>
                                        <button type="button" class="remove-attachment-btn" onclick="removeAttachment(<?php echo $media['id']; ?>)">
                                            ✕
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- 上传新附件 -->
                        <?php if (!empty($post['media_files'])): ?>
                        <div class="form-group">
                            <label class="form-label">添加新附件</label>
                            <div class="existing-attachments">
                                <div class="attachment-item add-attachment-item" onclick="document.getElementById('newAttachments').click()">
                                    <div class="add-attachment-content">
                                        <div class="add-attachment-icon">+</div>
                                        <div class="add-attachment-text">添加附件</div>
                                    </div>
                                </div>
                            </div>
                            <input type="file" id="newAttachments" name="new_attachments[]" 
                                   multiple accept="image/*,video/*" style="display: none;">
                            <div class="file-hint">支持图片和视频文件，最大50MB</div>
                        </div>
                        <?php else: ?>
                        <div class="form-group">
                            <label class="form-label">添加附件</label>
                            <div class="existing-attachments">
                                <div class="attachment-item add-attachment-item" onclick="document.getElementById('newAttachments').click()">
                                    <div class="add-attachment-content">
                                        <div class="add-attachment-icon">+</div>
                                        <div class="add-attachment-text">添加附件</div>
                                    </div>
                                </div>
                            </div>
                            <input type="file" id="newAttachments" name="new_attachments[]" 
                                   multiple accept="image/*,video/*" style="display: none;">
                            <div class="file-hint">支持图片和视频文件，最大50MB</div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-actions">
                            <a href="/Chat_System/forum/post?id=<?php echo $post['id']; ?>" class="btn btn-secondary">
                                ← 取消编辑
                            </a>
                            <div class="form-action-buttons">
                                <button type="button" class="btn btn-danger" onclick="deletePost(<?php echo $post['id']; ?>)">
                                    🗑️ 删除帖子
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    💾 保存修改
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="loading" id="loading">
                        <p>正在保存修改...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 字符计数功能
        function updateCharCount(input, counter, maxLength) {
            const count = input.value.length;
            counter.textContent = count + '/' + maxLength;
            
            if (count > maxLength * 0.9) {
                counter.className = 'char-count danger';
            } else if (count > maxLength * 0.8) {
                counter.className = 'char-count warning';
            } else {
                counter.className = 'char-count';
            }
        }
        
        // 初始化字符计数
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('postTitle');
            const contentInput = document.getElementById('postContent');
            const titleCount = document.getElementById('titleCount');
            const contentCount = document.getElementById('contentCount');
            
            // 初始计数
            updateCharCount(titleInput, titleCount, 200);
            updateCharCount(contentInput, contentCount, 5000);
            
            // 实时更新计数
            titleInput.addEventListener('input', function() {
                updateCharCount(this, titleCount, 200);
            });
            
            contentInput.addEventListener('input', function() {
                updateCharCount(this, contentCount, 5000);
            });
        });
        
        // 显示错误消息
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.classList.add('show');
            setTimeout(() => {
                errorDiv.classList.remove('show');
            }, 5000);
        }
        
        // 显示成功消息
        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            successDiv.textContent = message;
            successDiv.classList.add('show');
            setTimeout(() => {
                successDiv.classList.remove('show');
            }, 3000);
        }
        
        // 显示/隐藏加载状态
        function setLoading(show) {
            const loading = document.getElementById('loading');
            const form = document.getElementById('editPostForm');
            
            if (show) {
                loading.classList.add('show');
                form.style.opacity = '0.5';
                form.style.pointerEvents = 'none';
            } else {
                loading.classList.remove('show');
                form.style.opacity = '1';
                form.style.pointerEvents = 'auto';
            }
        }
        
        // 表单提交处理
        document.getElementById('editPostForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const title = document.getElementById('postTitle').value.trim();
            const content = document.getElementById('postContent').value.trim();
            const postId = document.getElementById('postId').value;
            
            if (!title || !content) {
                showError('标题和内容不能为空');
                return;
            }
            
            if (title.length > 200) {
                showError('标题不能超过200个字符');
                return;
            }
            
            if (content.length > 5000) {
                showError('内容不能超过5000个字符');
                return;
            }
            
            setLoading(true);
            
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('title', title);
            formData.append('content', content);
            
            // 添加新上传的文件
            const fileInput = document.getElementById('newAttachments');
            if (fileInput.files.length > 0) {
                for (let i = 0; i < fileInput.files.length; i++) {
                    formData.append('new_attachments[]', fileInput.files[i]);
                }
            }
            
            fetch('/Chat_System/forum/editPost', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                setLoading(false);
                if (data.success) {
                    showSuccess('帖子修改成功！');
                    setTimeout(() => {
                        window.location.href = '/Chat_System/forum/post?id=' + postId;
                    }, 1500);
                } else {
                    showError('修改失败: ' + data.message);
                }
            })
            .catch(error => {
                setLoading(false);
                console.error('Error:', error);
                showError('修改失败，请重试');
            });
        });
        
        // 删除帖子
        function deletePost(postId) {
            if (confirm('确定要删除这个帖子吗？删除后将无法恢复！')) {
                const formData = new FormData();
                formData.append('post_id', postId);
                
                fetch('/Chat_System/forum/deletePost', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('帖子删除成功！');
                        window.location.href = '/Chat_System/forum/view?id=<?php echo $post['forum_id']; ?>';
                    } else {
                        alert('删除失败: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('删除失败，请重试');
                });
            }
        }
        
         // 自动调整文本框高度
         document.getElementById('postContent').addEventListener('input', function() {
             this.style.height = 'auto';
             this.style.height = this.scrollHeight + 'px';
         });
         
         // 文件选择处理
         document.getElementById('newAttachments').addEventListener('change', function(e) {
             const files = e.target.files;
             if (files.length > 0) {
                 // 更新添加按钮的文本
                 const addButton = document.querySelector('.add-attachment-item');
                 if (addButton) {
                     const icon = addButton.querySelector('.add-attachment-icon');
                     const text = addButton.querySelector('.add-attachment-text');
                     
                     if (files.length === 1) {
                         text.textContent = files[0].name;
                     } else {
                         text.textContent = `已选择 ${files.length} 个文件`;
                     }
                     
                     // 改变样式表示已选择文件
                     addButton.style.borderColor = '#28a745';
                     addButton.style.backgroundColor = '#f8fff9';
                     icon.textContent = '✓';
                     icon.style.color = '#28a745';
                 }
             }
         });
        
        // 删除附件
        function removeAttachment(fileId) {
            if (confirm('确定要删除这个附件吗？')) {
                const attachmentItem = document.querySelector(`[data-file-id="${fileId}"]`);
                if (attachmentItem) {
                    attachmentItem.remove();
                }
                
                // 发送删除请求到服务器
                const formData = new FormData();
                formData.append('file_id', fileId);
                
                fetch('/Chat_System/forum/removeAttachment', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('删除附件失败:', data.message);
                    }
                })
                .catch(error => {
                    console.error('删除附件错误:', error);
                });
            }
        }
        
        // 打开媒体预览模态框
        function openMediaModal(filePath, type) {
            const modal = document.getElementById('mediaModal');
            const modalImage = document.getElementById('modalImage');
            const modalVideo = document.getElementById('modalVideo');
            
            if (type === 'image') {
                modalImage.src = filePath;
                modalImage.style.display = 'block';
                modalVideo.style.display = 'none';
            } else if (type === 'video') {
                modalVideo.src = filePath;
                modalVideo.style.display = 'block';
                modalImage.style.display = 'none';
            }
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        // 关闭媒体预览模态框
        function closeMediaModal() {
            const modal = document.getElementById('mediaModal');
            const modalImage = document.getElementById('modalImage');
            const modalVideo = document.getElementById('modalVideo');
            
            modal.style.display = 'none';
            modalImage.src = '';
            modalVideo.src = '';
            document.body.style.overflow = 'auto';
        }
    </script>
    
    <!-- 媒体预览模态框 -->
    <div id="mediaModal" class="media-modal">
        <span class="media-modal-close" onclick="closeMediaModal()">&times;</span>
        <div class="media-modal-content">
            <img id="modalImage" style="display: none;" alt="预览图片">
            <video id="modalVideo" style="display: none;" controls>
                您的浏览器不支持视频播放。
            </video>
        </div>
    </div>
    
    <style>
        /* 媒体预览模态框样式 */
        .media-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
        }
        
        .media-modal-content {
            position: relative;
            margin: auto;
            padding: 20px;
            width: 90%;
            max-width: 800px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .media-modal img,
        .media-modal video {
            width: 100%;
            height: auto;
            max-height: 80vh;
            object-fit: contain;
        }
        
        .media-modal-close {
            position: absolute;
            top: 10px;
            right: 25px;
            color: #fff;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10001;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }
        
        .media-modal-close:hover {
            background: rgba(0,0,0,0.8);
        }
        
        @media (max-width: 768px) {
            .media-modal-content {
                width: 95%;
                padding: 15px;
                max-width: none;
            }
            
            .media-modal-close {
                top: 5px;
                right: 15px;
                font-size: 30px;
                width: 45px;
                height: 45px;
            }
        }
        
        @media (max-width: 480px) {
            .media-modal-content {
                width: 98%;
                padding: 10px;
            }
            
            .media-modal-close {
                top: 5px;
                right: 10px;
                font-size: 25px;
                width: 40px;
                height: 40px;
            }
        }
    </style>
    
    <script>
        // 点击模态框背景关闭
        document.getElementById('mediaModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeMediaModal();
            }
        });
        
        // ESC键关闭模态框
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMediaModal();
            }
        });
    </script>
</body>
</html>
