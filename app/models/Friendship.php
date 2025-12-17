<?php
require_once BASE_PATH . '/config/Database.php';

class Friendship {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // 发送好友请求
    public function sendFriendRequest($userId, $friendId) {
        // 检查是否已经是好友
        if ($this->isFriend($userId, $friendId)) {
            return ['success' => false, 'message' => '已经是好友关系'];
        }
        
        // 检查是否已经发送过请求
        if ($this->hasPendingRequest($userId, $friendId)) {
            return ['success' => false, 'message' => '已经发送过好友请求'];
        }
        
        try {
            $sql = "INSERT INTO friendships (user_id, friend_id, status) VALUES (?, ?, 'pending')";
            $this->db->query($sql, [$userId, $friendId]);
            
            return ['success' => true, 'message' => '好友请求发送成功'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => '发送失败: ' . $e->getMessage()];
        }
    }
    
    // 接受好友请求
    public function acceptFriendRequest($userId, $friendId) {
        try {
            // 更新好友请求状态
            $sql = "UPDATE friendships SET status = 'accepted' WHERE user_id = ? AND friend_id = ? AND status = 'pending'";
            $this->db->query($sql, [$friendId, $userId]);
            
            // 创建反向好友关系
            $sql = "INSERT INTO friendships (user_id, friend_id, status) VALUES (?, ?, 'accepted')";
            $this->db->query($sql, [$userId, $friendId]);
            
            return ['success' => true, 'message' => '好友请求已接受'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => '接受失败: ' . $e->getMessage()];
        }
    }
    
    // 拒绝好友请求
    public function rejectFriendRequest($userId, $friendId) {
        try {
            $sql = "DELETE FROM friendships WHERE user_id = ? AND friend_id = ? AND status = 'pending'";
            $this->db->query($sql, [$friendId, $userId]);
            
            return ['success' => true, 'message' => '好友请求已拒绝'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => '拒绝失败: ' . $e->getMessage()];
        }
    }
    
    // 删除好友
    public function removeFriend($userId, $friendId) {
        try {
            $sql = "DELETE FROM friendships WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
            $this->db->query($sql, [$userId, $friendId, $friendId, $userId]);
            
            return ['success' => true, 'message' => '好友已删除'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => '删除失败: ' . $e->getMessage()];
        }
    }
    
    // 检查是否是好友
    public function isFriend($userId, $friendId) {
        $sql = "SELECT id FROM friendships WHERE user_id = ? AND friend_id = ? AND status = 'accepted'";
        return $this->db->fetch($sql, [$userId, $friendId]) !== false;
    }
    
    // 检查是否有待处理的请求
    public function hasPendingRequest($userId, $friendId) {
        $sql = "SELECT id FROM friendships WHERE user_id = ? AND friend_id = ? AND status = 'pending'";
        return $this->db->fetch($sql, [$userId, $friendId]) !== false;
    }
    
    // 获取好友关系状态
    public function getFriendshipStatus($userId, $friendId) {
        $sql = "SELECT status FROM friendships WHERE user_id = ? AND friend_id = ?";
        $result = $this->db->fetch($sql, [$userId, $friendId]);
        return $result ? $result['status'] : null;
    }
    
    // 设置好友备注
    public function setFriendNickname($userId, $friendId, $nickname) {
        $sql = "UPDATE friendships 
                SET nickname = ? 
                WHERE user_id = ? AND friend_id = ? AND status = 'accepted'";
        $result = $this->db->query($sql, [$nickname, $userId, $friendId]);
        return $result !== false;
    }
    
    // 获取好友备注
    public function getFriendNickname($userId, $friendId) {
        $sql = "SELECT nickname FROM friendships 
                WHERE user_id = ? AND friend_id = ? AND status = 'accepted'";
        $result = $this->db->query($sql, [$userId, $friendId]);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['nickname'] : null;
    }
}
?>
