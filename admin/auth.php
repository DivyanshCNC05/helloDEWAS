<?php
// auth.php - small helper to manage sessions and protect pages
session_start();

function ensure_logged_in() {
    if (empty($_SESSION['admin']) || empty($_SESSION['admin']['id'])) {
        header('Location: admin-login.html');
        exit();
    }
}

function login_admin($admin_row) {
    // store minimal info in session
    $_SESSION['admin'] = [
        'id' => $admin_row['id'],
        'email' => $admin_row['email'],
        'name' => $admin_row['name'] ?? null,
    ];
    // regenerate session id to mitigate fixation
    session_regenerate_id(true);
}

function logout_admin() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
