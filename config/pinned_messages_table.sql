-- 创建置顶消息表
CREATE TABLE IF NOT EXISTS `pinned_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_message` (`user_id`, `message_id`),
  KEY `idx_message_id` (`message_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_pinned_message` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pinned_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 为messages表添加is_recalled和is_deleted字段（如果不存在）
ALTER TABLE `messages` 
ADD COLUMN IF NOT EXISTS `is_recalled` tinyint(1) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

-- 添加索引
ALTER TABLE `messages` 
ADD INDEX IF NOT EXISTS `idx_is_recalled` (`is_recalled`),
ADD INDEX IF NOT EXISTS `idx_is_deleted` (`is_deleted`);
