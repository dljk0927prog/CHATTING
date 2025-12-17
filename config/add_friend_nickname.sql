-- 为好友关系表添加备注字段
ALTER TABLE friendships ADD COLUMN nickname VARCHAR(100) DEFAULT NULL COMMENT '好友备注名称';

-- 添加索引以提高查询性能
CREATE INDEX idx_friendships_nickname ON friendships(nickname);
