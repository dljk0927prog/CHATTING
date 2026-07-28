<?php
// 重定向到语音/视频通话页面，保留所有参数
$room_id = isset($_GET['roomId']) ? (int)$_GET['roomId'] : 0;
$call_type = isset($_GET['callType']) ? $_GET['callType'] : 'voice';

if ($room_id > 0) {
    $params = ['roomId' => $room_id, 'callType' => $call_type];
    if (!empty($_GET['fromUserId'])) $params['fromUserId'] = $_GET['fromUserId'];
    if (!empty($_GET['fromUsername'])) $params['fromUsername'] = $_GET['fromUsername'];
    if (isset($_GET['isIncoming'])) $params['isIncoming'] = $_GET['isIncoming'];
    if (!empty($_GET['callId'])) $params['callId'] = $_GET['callId'];
    $q = http_build_query($params);
    header("Location: /Chat_System/chat/simple_call?$q");
    exit();
} else {
    die('Invalid room ID');
}
?>