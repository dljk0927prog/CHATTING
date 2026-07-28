<?php
/**
 * 通话信令控制器 - 用于 WebRTC offer/answer/ice/end 信令交换（数据库轮询）
 */
class CallSignalController {
    private $pdo;
    private $currentUserId;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->currentUserId = $_SESSION['user_id'] ?? 0;
    }

    /** 推送信令 */
    public function push() {
        header('Content-Type: application/json');
        if (!$this->currentUserId) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            return;
        }
        $room_id = isset($_REQUEST['room_id']) ? (int)$_REQUEST['room_id'] : 0;
        $receiver_id = isset($_REQUEST['receiver_id']) ? (int)$_REQUEST['receiver_id'] : 0;
        if ($room_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid room ID']);
            return;
        }
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        $payload = isset($_POST['payload']) ? $_POST['payload'] : '';
        if (!$type || $payload === '') {
            echo json_encode(['success' => false, 'error' => 'Missing type or payload']);
            return;
        }
        try {
            if ($receiver_id <= 0) {
                $stmt = $this->pdo->prepare("SELECT user_id FROM chat_room_members WHERE room_id = ? AND user_id != ?");
                $stmt->execute([$room_id, $this->currentUserId]);
                $members = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($members as $uid) {
                    $ins = $this->pdo->prepare("INSERT INTO call_signals (room_id, sender_id, receiver_id, signal_type, payload) VALUES (?, ?, ?, ?, ?)");
                    $ins->execute([$room_id, $this->currentUserId, $uid, $type, $payload]);
                }
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO call_signals (room_id, sender_id, receiver_id, signal_type, payload) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$room_id, $this->currentUserId, $receiver_id, $type, $payload]);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /** 轮询拉取信令 */
    public function poll() {
        header('Content-Type: application/json');
        if (!$this->currentUserId) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            return;
        }
        $room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
        if ($room_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid room ID']);
            return;
        }
        try {
            $stmt = $this->pdo->prepare("SELECT id, signal_type, payload FROM call_signals WHERE room_id = ? AND receiver_id = ? AND is_consumed = 0 ORDER BY id ASC");
            $stmt->execute([$room_id, $this->currentUserId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $ids = array_column($rows, 'id');
            if (!empty($ids)) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $mark = $this->pdo->prepare("UPDATE call_signals SET is_consumed = 1 WHERE id IN ($in)");
                $mark->execute($ids);
            }
            echo json_encode(['success' => true, 'signals' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /** 轮询来电（未消费的 offer） */
    public function poll_incoming() {
        header('Content-Type: application/json');
        if (!$this->currentUserId) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            return;
        }
        try {
            $stmt = $this->pdo->prepare("SELECT cs.id, cs.room_id, cs.sender_id, u.username, u.avatar
                FROM call_signals cs
                JOIN users u ON u.id = cs.sender_id
                WHERE cs.receiver_id = ? AND cs.is_consumed = 0 AND cs.signal_type = 'offer'
                ORDER BY cs.id DESC LIMIT 5");
            $stmt->execute([$this->currentUserId]);
            $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'offers' => $offers]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /** 结束通话：向房间内其他人发送 end */
    public function end() {
        header('Content-Type: application/json');
        if (!$this->currentUserId) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            return;
        }
        $room_id = isset($_REQUEST['room_id']) ? (int)$_REQUEST['room_id'] : 0;
        if ($room_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid room ID']);
            return;
        }
        try {
            $stmt = $this->pdo->prepare("SELECT user_id FROM chat_room_members WHERE room_id = ? AND user_id != ?");
            $stmt->execute([$room_id, $this->currentUserId]);
            $members = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $ins = $this->pdo->prepare("INSERT INTO call_signals (room_id, sender_id, receiver_id, signal_type, payload) VALUES (?, ?, ?, 'end', '{}')");
            foreach ($members as $uid) {
                $ins->execute([$room_id, $this->currentUserId, $uid]);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
