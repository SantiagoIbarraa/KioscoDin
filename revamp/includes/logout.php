<?php
session_start();

$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/kioscoDin/revamp';

$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header("Location: $base_url/pages/login/");
exit();
?>
