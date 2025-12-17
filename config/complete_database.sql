-- =====================================================
-- 聊天系统完整数据库结构
-- 版本: 2.0
-- 创建时间: 2025-01-27
-- 描述: 包含所有功能的完整数据库结构
-- =====================================================

-- 创建数据库
CREATE DATABASE IF NOT EXISTS chatting_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE chatting_system;

-- =====================================================
-- 1. 核心用户系统
-- =====================================================

-- 用户表
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default_avatar.png',
    status ENUM('online', 'offline', 'away') DEFAULT 'offline',
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_username (username),
    INDEX idx_users_email (email),
    INDEX idx_users_status (status)
);

-- 好友关系表
CREATE TABLE friendships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'blocked') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_friendship (user_id, friend_id),
    INDEX idx_friendships_user_id (user_id),
    INDEX idx_friendships_friend_id (friend_id),
    INDEX idx_friendships_status (status)
);

-- =====================================================
-- 2. 聊天系统
-- =====================================================

-- 聊天室表
CREATE TABLE chat_rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    type ENUM('private', 'group') DEFAULT 'private',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_chat_rooms_type (type),
    INDEX idx_chat_rooms_created_by (created_by)
);

-- 聊天室成员表
CREATE TABLE chat_room_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('member', 'admin', 'creator') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_membership (room_id, user_id),
    INDEX idx_chat_room_members_room_id (room_id),
    INDEX idx_chat_room_members_user_id (user_id),
    INDEX idx_chat_room_members_role (role)
);

-- 消息表
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT NOT NULL,
    sender_id INT NOT NULL,
    content TEXT NOT NULL,
    message_type ENUM('text', 'image', 'file', 'voice', 'video') DEFAULT 'text',
    file_path TEXT,
    file_size BIGINT DEFAULT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    is_edited BOOLEAN DEFAULT FALSE,
    is_recalled BOOLEAN DEFAULT FALSE,
    is_pinned BOOLEAN DEFAULT FALSE,
    quoted_message_id INT NULL,
    quoted_content TEXT NULL,
    quoted_username VARCHAR(50) NULL,
    quoted_type ENUM('text', 'file') NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quoted_message_id) REFERENCES messages(id) ON DELETE SET NULL,
    INDEX idx_messages_room_id (room_id),
    INDEX idx_messages_sender_id (sender_id),
    INDEX idx_messages_created_at (created_at),
    INDEX idx_messages_message_type (message_type),
    INDEX idx_messages_is_pinned (is_pinned)
);

-- 消息已读状态表
CREATE TABLE message_read_status (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_read_status (message_id, user_id),
    INDEX idx_message_read_status_message_id (message_id),
    INDEX idx_message_read_status_user_id (user_id)
);

-- =====================================================
-- 3. 通话系统
-- =====================================================

-- 通话邀请表
CREATE TABLE call_invitations (
    id VARCHAR(100) PRIMARY KEY,
    room_id INT NOT NULL,
    caller_id INT NOT NULL,
    caller_name VARCHAR(100) NOT NULL,
    call_type ENUM('voice', 'video') NOT NULL,
    target_user_id INT NULL COMMENT '目标用户ID，用于私聊通话',
    status ENUM('inviting', 'accepted', 'rejected', 'cancelled', 'timeout') DEFAULT 'inviting',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (caller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_call_invitations_room_id (room_id),
    INDEX idx_call_invitations_caller_id (caller_id),
    INDEX idx_call_invitations_target_user_id (target_user_id),
    INDEX idx_call_invitations_status (status),
    INDEX idx_call_invitations_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. 收藏系统
-- =====================================================

-- 收藏表
CREATE TABLE favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    message_id INT NULL,
    type ENUM('text', 'image', 'video', 'file', 'link') DEFAULT 'text',
    title VARCHAR(255) NOT NULL,
    content TEXT,
    file_path TEXT,
    file_size BIGINT,
    thumbnail VARCHAR(255),
    metadata JSON,
    tags VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    INDEX idx_favorites_user_id (user_id),
    INDEX idx_favorites_message_id (message_id),
    INDEX idx_favorites_type (type),
    INDEX idx_favorites_created_at (created_at)
);

-- =====================================================
-- 5. 论坛系统
-- =====================================================

-- 论坛表
CREATE TABLE forums (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    avatar VARCHAR(255) DEFAULT 'default_forum_avatar.png',
    creator_id INT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    max_members INT DEFAULT 1000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_forums_creator_id (creator_id),
    INDEX idx_forums_created_at (created_at),
    INDEX idx_forums_is_public (is_public)
);

-- 论坛成员表
CREATE TABLE forum_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    forum_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('member', 'admin', 'creator') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_forum_membership (forum_id, user_id),
    INDEX idx_forum_members_forum_id (forum_id),
    INDEX idx_forum_members_user_id (user_id),
    INDEX idx_forum_members_role (role)
);

-- 论坛加入请求表
CREATE TABLE forum_join_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    forum_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    processed_by INT NULL,
    FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_forum_request (forum_id, user_id),
    INDEX idx_forum_requests_forum_id (forum_id),
    INDEX idx_forum_requests_user_id (user_id),
    INDEX idx_forum_requests_status (status)
);

-- 论坛邀请请求表
CREATE TABLE forum_invite_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    forum_id INT NOT NULL,
    invited_user_id INT NOT NULL,
    invited_by_user_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'expired') DEFAULT 'pending',
    message TEXT NULL,
    invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_forum_invite (forum_id, invited_user_id),
    INDEX idx_forum_invite_forum_id (forum_id),
    INDEX idx_forum_invite_invited_user_id (invited_user_id),
    INDEX idx_forum_invite_invited_by_user_id (invited_by_user_id),
    INDEX idx_forum_invite_status (status),
    INDEX idx_forum_invite_expires_at (expires_at)
);

-- 论坛帖子表
CREATE TABLE forum_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    forum_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    post_type ENUM('text', 'image', 'link') DEFAULT 'text',
    is_pinned BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_forum_posts_forum_id (forum_id),
    INDEX idx_forum_posts_user_id (user_id),
    INDEX idx_forum_posts_created_at (created_at),
    INDEX idx_forum_posts_is_pinned (is_pinned)
);

-- 论坛帖子文件表
CREATE TABLE forum_post_files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES forum_posts(id) ON DELETE CASCADE,
    INDEX idx_forum_post_files_post_id (post_id),
    INDEX idx_forum_post_files_uploaded_at (uploaded_at)
);

-- 论坛回复表
CREATE TABLE forum_replies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    reply_to INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES forum_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reply_to) REFERENCES forum_replies(id) ON DELETE CASCADE,
    INDEX idx_forum_replies_post_id (post_id),
    INDEX idx_forum_replies_user_id (user_id),
    INDEX idx_forum_replies_created_at (created_at)
);

-- =====================================================
-- 6. 初始化数据
-- =====================================================

-- 插入测试用户
INSERT INTO users (username, email, password) VALUES 
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('philia', 'philia@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 插入示例论坛数据
INSERT INTO forums (name, description, creator_id, is_public) VALUES 
('技术讨论', '分享编程技术、开发经验和学习心得', 1, TRUE),
('生活分享', '分享日常生活、旅行见闻和生活感悟', 1, TRUE),
('游戏交流', '讨论游戏攻略、心得和组队信息', 1, TRUE);

-- 将创建者自动添加为论坛成员
INSERT INTO forum_members (forum_id, user_id, role) 
SELECT id, creator_id, 'creator' FROM forums;

-- 为现有用户添加一些论坛成员关系
INSERT IGNORE INTO forum_members (forum_id, user_id, role) VALUES 
(1, 1, 'creator'),
(1, 2, 'member'),
(1, 3, 'member'),
(2, 1, 'creator'),
(2, 3, 'member'),
(3, 1, 'creator'),
(3, 2, 'member');

-- =====================================================
-- 7. 完成提示
-- =====================================================

-- 显示创建完成的表
SELECT 'Database setup completed successfully!' as status;
SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = 'chatting_system';
