<?php
require_once __DIR__ . '/../models/Security.php';
require_once __DIR__ . '/../config/app.php';

Security::initSession();

if (Security::isLoggedIn()) {
    header('Location: ' . APP_URL . '/views/dashboard/index.php');
} else {
    header('Location: ' . APP_URL . '/views/auth/login.php');
}
exit;
