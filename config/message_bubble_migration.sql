-- 消息气泡栏功能数据库迁移脚本
-- 执行此脚本来添加必要的数据库表和字段

-- 1. 创建置顶消息表
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

-- 2. 为messages表添加新字段（如果不存在）
-- 检查并添加is_recalled字段
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'messages' 
     AND table_schema = DATABASE() 
     AND column_name = 'is_recalled') = 0,
    'ALTER TABLE `messages` ADD COLUMN `is_recalled` tinyint(1) NOT NULL DEFAULT 0',
    'SELECT "is_recalled column already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加is_deleted字段
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'messages' 
     AND table_schema = DATABASE() 
     AND column_name = 'is_deleted') = 0,
    'ALTER TABLE `messages` ADD COLUMN `is_deleted` tinyint(1) NOT NULL DEFAULT 0',
    'SELECT "is_deleted column already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加updated_at字段
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'messages' 
     AND table_schema = DATABASE() 
     AND column_name = 'updated_at') = 0,
    'ALTER TABLE `messages` ADD COLUMN `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP',
    'SELECT "updated_at column already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. 添加索引（如果不存在）
-- 为is_recalled字段添加索引
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE table_name = 'messages' 
     AND table_schema = DATABASE() 
     AND index_name = 'idx_is_recalled') = 0,
    'ALTER TABLE `messages` ADD INDEX `idx_is_recalled` (`is_recalled`)',
    'SELECT "idx_is_recalled index already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 为is_deleted字段添加索引
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE table_name = 'messages' 
     AND table_schema = DATABASE() 
     AND index_name = 'idx_is_deleted') = 0,
    'ALTER TABLE `messages` ADD INDEX `idx_is_deleted` (`is_deleted`)',
    'SELECT "idx_is_deleted index already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. 确保favorites表存在metadata字段（用于存储消息ID）
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'favorites' 
     AND table_schema = DATABASE() 
     AND column_name = 'metadata') = 0,
    'ALTER TABLE `favorites` ADD COLUMN `metadata` JSON NULL',
    'SELECT "metadata column already exists in favorites table"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 完成迁移
SELECT 'Message bubble functionality migration completed successfully!' as status;
