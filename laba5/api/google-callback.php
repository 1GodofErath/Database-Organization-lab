<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../vendor/autoload.php';

$googleConfig = require __DIR__ . '/google-config.php';

$client = new Google_Client();
$client->setClientId($googleConfig['client_id']);
$client->setClientSecret($googleConfig['client_secret']);
$client->setRedirectUri($googleConfig['redirect_uri']);
$client->addScope("email");
$client->addScope("profile");

try {
    if (!isset($_GET['code'])) {
        throw new Exception("Код авторизації не отримано");
    }

    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        throw new Exception($token['error_description'] ?? 'Помилка отримання токену');
    }

    $client->setAccessToken($token['access_token']);

    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();

    $email = $google_account_info->email;
    $name = $google_account_info->name;
    $google_id = $google_account_info->id;

    $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        if (empty($user['password'])) {
            $_SESSION['google_user'] = [
                'email' => $email,
                'name' => $name,
                'google_id' => $google_id,
                'user_id' => $user['id']
            ];
            header("Location: set-password.php");
            exit;
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            error_log("User logged in via Google: " . $email);
            header("Location: /dah/dashboard.php");
            exit;
        }
    } else {
        $_SESSION['google_user'] = [
            'email' => $email,
            'name' => $name,
            'google_id' => $google_id
        ];
        header("Location: set-password.php");
        exit;
    }
} catch (Exception $e) {
    error_log("Google OAuth error: " . $e->getMessage());
    $_SESSION['error'] = "Помилка авторізації через Google. Спробуйте ще раз.";
    header("Location: /login.php");
    exit;
}
