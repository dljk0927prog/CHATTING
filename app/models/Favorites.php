<?php
require_once BASE_PATH . '/config/Database.php';

class Favorites {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // 添加收藏
    public function addFavorite($userId, $type, $title, $content = null, $filePath = null, $fileSize = null, $thumbnail = null, $metadata = null, $tags = null) {
        try {
            $sql = "INSERT INTO favorites (user_id, type, title, content, file_path, file_size, thumbnail, metadata, tags) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $metadataJson = $metadata ? json_encode($metadata) : null;
            
            $this->db->query($sql, [
                $userId, $type, $title, $content, $filePath, $fileSize, $thumbnail, $metadataJson, $tags
            ]);
            
            return ['success' => true, 'message' => '收藏成功', 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => '收藏失败: ' . $e->getMessage()];
        }
    }
    
    // 获取用户收藏列表
    public function getUserFavorites($userId, $type = null, $limit = 50, $offset = 0) {
        $sql = "SELECT * FROM favorites WHERE user_id = ?";
        $params = [$userId];
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // 根据ID获取收藏
    public function getFavoriteById($favoriteId, $userId) {
        $sql = "SELECT * FROM favorites WHERE id = ? AND user_id = ?";
        return $this->db->fetch($sql, [$favoriteId, $userId]);
    }
    
    // 删除收藏
    public function deleteFavorite($favoriteId, $userId) {
        try {
            $sql = "DELETE FROM favorites WHERE id = ? AND user_id = ?";
            $this->db->query($sql, [$favoriteId, $userId]);
            
            return ['success' => true, 'message' => '删除成功'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => '删除失败: ' . $e->getMessage()];
        }
    }
    
    // 更新收藏
    public function updateFavorite($favoriteId, $userId, $title, $content = null, $tags = null) {
        try {
            $sql = "UPDATE favorites SET title = ?, content = ?, tags = ?, updated_at = NOW() 
                    WHERE id = ? AND user_id = ?";
            $this->db->query($sql, [$title, $content, $tags, $favoriteId, $userId]);
            
            return ['success' => true, 'message' => '更新成功'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => '更新失败: ' . $e->getMessage()];
        }
    }
    
    // 搜索收藏
    public function searchFavorites($userId, $keyword, $type = null) {
        $sql = "SELECT * FROM favorites WHERE user_id = ? AND (title LIKE ? OR content LIKE ? OR tags LIKE ?)";
        $params = [$userId, "%{$keyword}%", "%{$keyword}%", "%{$keyword}%"];
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // 获取收藏统计
    public function getFavoritesStats($userId) {
        $sql = "SELECT type, COUNT(*) as count FROM favorites WHERE user_id = ? GROUP BY type";
        $stats = $this->db->fetchAll($sql, [$userId]);
        
        $result = [
            'total' => 0,
            'by_type' => []
        ];
        
        foreach ($stats as $stat) {
            $result['by_type'][$stat['type']] = $stat['count'];
            $result['total'] += $stat['count'];
        }
        
        return $result;
    }
    
    // 切换收藏状态
    public function toggleFavorite($userId, $messageId, $message) {
        try {
            // 检查是否已收藏
            $sql = "SELECT id FROM favorites WHERE user_id = ? AND JSON_EXTRACT(metadata, '$.message_id') = ?";
            $existing = $this->db->fetch($sql, [$userId, $messageId]);
            
            if ($existing) {
                // 取消收藏
                $sql = "DELETE FROM favorites WHERE id = ?";
                $this->db->query($sql, [$existing['id']]);
                return false;
            } else {
                // 添加收藏
                $title = $this->generateFavoriteTitle($message);
                $content = $message['content'] ?? '';
                $filePath = $message['file_path'] ?? null;
                $fileSize = null;
                $thumbnail = null;
                $metadata = [
                    'message_id' => $messageId,
                    'room_id' => $message['room_id'],
                    'sender_id' => $message['sender_id'],
                    'message_type' => $message['message_type'],
                    'created_at' => $message['created_at']
                ];
                $tags = $this->generateTags($message);
                
                $this->addFavorite($userId, $message['message_type'], $title, $content, $filePath, $fileSize, $thumbnail, $metadata, $tags);
                return true;
            }
        } catch (Exception $e) {
            error_log("切换收藏状态失败: " . $e->getMessage());
            return null; // 返回 null 表示操作失败
        }
    }
    
    // 生成收藏标题
    private function generateFavoriteTitle($message) {
        $type = $message['message_type'] ?? 'text';
        $content = $message['content'] ?? '';
        
        switch ($type) {
            case 'text':
                return mb_substr($content, 0, 50) . (mb_strlen($content) > 50 ? '...' : '');
            case 'image':
                return '图片消息';
            case 'video':
                return '视频消息';
            case 'voice':
                return '语音消息';
            case 'file':
                return '文件消息';
            default:
                return '消息';
        }
    }
    
    // 生成标签
    private function generateTags($message) {
        $tags = [];
        $type = $message['message_type'] ?? 'text';
        
        switch ($type) {
            case 'image':
                $tags[] = '图片';
                break;
            case 'video':
                $tags[] = '视频';
                break;
            case 'voice':
                $tags[] = '语音';
                break;
            case 'file':
                $tags[] = '文件';
                break;
            default:
                $tags[] = '文本';
        }
        
        return implode(',', $tags);
    }
}
?>
