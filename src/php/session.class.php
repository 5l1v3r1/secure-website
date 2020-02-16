<?php
class Session {
    private function startSession() {
        if (!session_id()) {
            if (session_name() !== 'sws_session') {
                session_name('sws_session');
                // if you already have an HTTPS protocol configured you can set the secure cookie flag to true
                session_set_cookie_params(24 * 3600, '/', '', false, true);
            }
            session_start();
        }
    }
    static function setUser($user) {
        if (is_a($user, 'User')) {
            self::startSession();
            session_regenerate_id(true);
            session_id();
            $_SESSION['user'] = $user;
            return true;
        } else {
            return false;
        }
    }
    static function getUser() {
        self::startSession();
        if (isset($_SESSION['user'])) {
            if (is_a($_SESSION['user'], 'User')) {
                if ($_SESSION['user']->getRemoteAddr() === $_SERVER['REMOTE_ADDR']) {
                    return $_SESSION['user'];
                } else {
                    // log a possible session hijacking
                    self::deleteSession();
                    return null;
                }
            } else {
                self::deleteSession();
                return null;
            }
        } else {
            return null;
        }
    }
    static function deleteSession() {
        self::startSession();
        if (session_id()) {
            if (ini_get('session.use_cookies')) {
                $parameters = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $parameters['path'], $parameters['domain'], $parameters['secure'], $parameters['httponly']);
            }
            session_destroy();
        }
    }
    public function generateToken($name) {
        self::startSession();
        $token = array('value' => bin2hex(random_bytes(64)), 'expiration' => time() + 60 * 15);
        $_SESSION['tokens'][$name . '_token'] = $token;
        return $token['value'];
    }
    public function verifyToken($value, $name) {
        self::startSession();
        $status = true;
        if (isset($_SESSION['tokens'][$name . '_token'])) {
            $token = $_SESSION['tokens'][$name . '_token'];
            if ($value !== $token['value'] || time() > $token['expiration']) {
                // log a possible CSRF (Cross-Site Request Forgery)
                $status = false;
            }
        } else {
            $status = false;
        }
        return $status;
    }
}
?>
