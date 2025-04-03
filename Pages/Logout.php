<?php

class UserSession {
    public static function logout() {
        session_start();
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

UserSession::logout();

?>
