<?php
// 重定向到新的简化语音通话页面
$room_id = isset($_GET['roomId']) ? (int)$_GET['roomId'] : 0;
$call_type = isset($_GET['callType']) ? $_GET['callType'] : 'voice';

if ($room_id > 0) {
    header("Location: simple_call.php?roomId=$room_id&callType=$call_type");
    exit();
                                } else {
    die('Invalid room ID');
}
?>