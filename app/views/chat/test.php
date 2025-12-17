<?php
echo "Test page working!<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
echo "Room ID: " . (isset($_GET['roomId']) ? $_GET['roomId'] : 'not set') . "<br>";
echo "Call Type: " . (isset($_GET['callType']) ? $_GET['callType'] : 'not set') . "<br>";
echo "Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . "<br>";
echo "File path: " . __FILE__ . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
?>
