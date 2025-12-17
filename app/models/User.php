<?php
require_once BASE_PATH . '/config/Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // 用户注册
    public function register($username, $email, $password) {
        // 检查用户名和邮箱是否已存在
        if ($this->usernameExists($username)) {
            return ['success' => false, 'message' => '用户名已存在'];
        }
        
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => '邮箱已存在'];
        }
        
        // 加密密码
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $this->db->query($sql, [$username, $email, $hashedPassword]);
            
            return ['success' => true, 'message' => '注册成功'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => '注册失败: ' . $e->getMessage()];
        }
    }
    
    // 用户登录
    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $user = $this->db->fetch($sql, [$username, $username]);
        
        if ($user && password_verify($password, $user['password'])) {
            // 更新用户状态为在线
            $this->updateStatus($user['id'], 'online');
            return ['success' => true, 'user' => $user];
        }
        
        return ['success' => false, 'message' => '用户名或密码错误'];
    }
    
    // 检查用户名是否存在
    private function usernameExists($username) {
        $sql = "SELECT id FROM users WHERE username = ?";
        return $this->db->fetch($sql, [$username]) !== false;
    }
    
    // 检查邮箱是否存在
    private function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = ?";
        return $this->db->fetch($sql, [$email]) !== false;
    }
    
    // 更新用户状态
    public function updateStatus($userId, $status) {
        $sql = "UPDATE users SET status = ?, last_seen = NOW() WHERE id = ?";
        $this->db->query($sql, [$status, $userId]);
    }
    
    // 更新用户头像
    public function updateAvatar($userId, $avatar) {
        try {
            $sql = "UPDATE users SET avatar = ? WHERE id = ?";
            $this->db->query($sql, [$avatar, $userId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // 更新用户名
    public function updateUsername($userId, $username) {
        try {
            $sql = "UPDATE users SET username = ? WHERE id = ?";
            $this->db->query($sql, [$username, $userId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // 更新邮箱
    public function updateEmail($userId, $email) {
        try {
            $sql = "UPDATE users SET email = ? WHERE id = ?";
            $this->db->query($sql, [$email, $userId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // 更新密码
    public function updatePassword($userId, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $this->db->query($sql, [$hashedPassword, $userId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // 检查用户名是否存在（排除指定用户）
    public function isUsernameExists($username, $excludeUserId = null) {
        $sql = "SELECT id FROM users WHERE username = ?";
        $params = [$username];
        
        if ($excludeUserId) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
        }
        
        return $this->db->fetch($sql, $params) !== false;
    }

    // 检查邮箱是否存在（排除指定用户）
    public function isEmailExists($email, $excludeUserId = null) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeUserId) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
        }
        
        return $this->db->fetch($sql, $params) !== false;
    }
    
    // 获取用户信息
    public function getUserById($userId) {
        $sql = "SELECT id, username, email, avatar, status, last_seen, created_at FROM users WHERE id = ?";
        return $this->db->fetch($sql, [$userId]);
    }

    // 获取用户信息（包含密码，用于验证）
    public function getUserByIdWithPassword($userId) {
        $sql = "SELECT id, username, email, password, avatar, status, last_seen, created_at FROM users WHERE id = ?";
        return $this->db->fetch($sql, [$userId]);
    }
    
    // 通过用户名获取用户信息
    public function getUserByUsername($username) {
        $sql = "SELECT id, username, email, avatar, status, last_seen, created_at FROM users WHERE username = ?";
        return $this->db->fetch($sql, [$username]);
    }
    
    // 搜索用户
    public function searchUsers($keyword, $excludeUserId = null) {
        $sql = "SELECT id, username, email, avatar, status FROM users 
                WHERE (username LIKE ? OR email LIKE ?)";
        $params = ["%{$keyword}%", "%{$keyword}%"];
        
        if ($excludeUserId) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
        }
        
        $sql .= " LIMIT 20";
        return $this->db->fetchAll($sql, $params);
    }
    
    // 获取用户好友列表
    public function getFriends($userId) {
        $sql = "SELECT u.id, u.username, u.email, u.avatar, u.status, u.last_seen, f.created_at as friendship_date, f.nickname
                FROM friendships f
                JOIN users u ON f.friend_id = u.id
                LEFT JOIN blocked_users bu ON (bu.blocker_id = ? AND bu.blocked_id = u.id) OR (bu.blocker_id = u.id AND bu.blocked_id = ?)
                WHERE f.user_id = ? AND f.status = 'accepted' AND bu.id IS NULL
                ORDER BY u.status DESC, u.last_seen DESC";
        return $this->db->fetchAll($sql, [$userId, $userId, $userId]);
    }
    
    // 获取待处理的好友请求
    public function getPendingRequests($userId) {
        $sql = "SELECT u.id, u.username, u.email, u.avatar, f.created_at as request_date
                FROM friendships f
                JOIN users u ON f.user_id = u.id
                WHERE f.friend_id = ? AND f.status = 'pending'
                ORDER BY f.created_at DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    // 获取用户已加入的论坛列表
    public function getUserForums($userId) {
        $sql = "SELECT f.id, f.name, f.description, f.avatar, f.created_at, 
                       fm.role, fm.joined_at,
                       (SELECT COUNT(*) FROM forum_members fm2 WHERE fm2.forum_id = f.id) as member_count,
                       (SELECT COUNT(*) FROM forum_posts fp WHERE fp.forum_id = f.id) as post_count
                FROM forums f
                JOIN forum_members fm ON f.id = fm.forum_id
                WHERE fm.user_id = ?
                ORDER BY f.name ASC";
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    // 获取用户的论坛邀请列表
    public function getUserForumInvites($userId) {
        $sql = "SELECT fir.id, fir.forum_id, fir.message, fir.invited_at, fir.expires_at,
                       f.name as forum_name, f.description as forum_description, f.avatar as forum_avatar,
                       u.username as inviter_username, u.avatar as inviter_avatar
                FROM forum_invite_requests fir
                JOIN forums f ON fir.forum_id = f.id
                JOIN users u ON fir.invited_by_user_id = u.id
                WHERE fir.invited_user_id = ? AND fir.status = 'pending'
                ORDER BY fir.invited_at DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }
}
?>
