<?php
require_once __DIR__ . '/db.php';

function login_user(string $email, string $password): bool {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

function register_user(string $name, string $email, string $password): array {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        return ['ok' => false, 'error' => 'Email already registered'];
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (:name, :email, :hash, "reader", NOW())');
    $stmt->execute([':name' => $name, ':email' => $email, ':hash' => $hash]);
    return ['ok' => true];
}

function require_login(): void {
    if (!current_user_id()) {
        header('Location: ' . AppConfig::baseUrl() . 'login.php');
        exit;
    }
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}