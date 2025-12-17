<?php
session_start();
require_once '../../config/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    return;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'poll';
$current_user_id = $_SESSION['user_id'];
$room_id = isset($_REQUEST['room_id']) ? (int)$_REQUEST['room_id'] : 0;
$receiver_id = isset($_REQUEST['receiver_id']) ? (int)$_REQUEST['receiver_id'] : 0;

// room_id对于某些操作是必需的
if ($action !== 'poll_incoming') {
    if ($room_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid room ID']);
        return;
    }
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    if ($action === 'push') {
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        $payload = isset($_POST['payload']) ? $_POST['payload'] : '';
        if (!$type || !$payload) {
            echo json_encode(['success' => false, 'error' => 'Missing type or payload']);
            return;
        }
        
        // 对于群组通话，receiver_id可以是0，表示发送给房间内所有其他用户
        if ($receiver_id <= 0) {
            // 获取房间内所有其他成员
            $stmt = $pdo->prepare("SELECT user_id FROM chat_room_members WHERE room_id = ? AND user_id != ?");
            $stmt->execute([$room_id, $current_user_id]);
            $members = $stmt->fetchAll();
            
            foreach ($members as $member) {
                $stmt = $pdo->prepare("INSERT INTO call_signals (room_id, sender_id, receiver_id, signal_type, payload) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$room_id, $current_user_id, $member['user_id'], $type, $payload]);
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO call_signals (room_id, sender_id, receiver_id, signal_type, payload) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$room_id, $current_user_id, $receiver_id, $type, $payload]);
        }
        
        echo json_encode(['success' => true]);
        return;
    }

    if ($action === 'poll') {
        $stmt = $pdo->prepare("SELECT id, signal_type, payload FROM call_signals WHERE room_id = ? AND receiver_id = ? AND is_consumed = 0 ORDER BY id ASC");
        $stmt->execute([$room_id, $current_user_id]);
        $rows = $stmt->fetchAll();
        $ids = array_map(function($r){ return $r['id']; }, $rows);
        if (!empty($ids)) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $mark = $pdo->prepare("UPDATE call_signals SET is_consumed = 1 WHERE id IN ($in)");
            $mark->execute($ids);
        }
        echo json_encode(['success' => true, 'signals' => $rows]);
        return;
    }

    if ($action === 'poll_incoming') {
        // 检查所有房间中未消费的offer
        $stmt = $pdo->prepare("SELECT cs.id, cs.room_id, cs.sender_id, u.username, u.avatar
            FROM call_signals cs
            JOIN users u ON u.id = cs.sender_id
            WHERE cs.receiver_id = ? AND cs.is_consumed = 0 AND cs.signal_type = 'offer'
            ORDER BY cs.id DESC LIMIT 5");
        $stmt->execute([$current_user_id]);
        $offers = $stmt->fetchAll();
        echo json_encode(['success' => true, 'offers' => $offers]);
        return;
    }

    if ($action === 'end') {
        // 对于群组通话，发送end信号给房间内所有其他用户
        $stmt = $pdo->prepare("SELECT user_id FROM chat_room_members WHERE room_id = ? AND user_id != ?");
        $stmt->execute([$room_id, $current_user_id]);
        $members = $stmt->fetchAll();
        
        foreach ($members as $member) {
            $stmt = $pdo->prepare("INSERT INTO call_signals (room_id, sender_id, receiver_id, signal_type, payload) VALUES (?, ?, ?, 'end', '{}')");
            $stmt->execute([$room_id, $current_user_id, $member['user_id']]);
        }
        
        echo json_encode(['success' => true]);
        return;
    }

    echo json_encode(['success' => false, 'error' => 'Invalid action']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
