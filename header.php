<?php
/**
 * Лабораторная работа № 2. Задание 3.
 * Шапка сайта — подключается ко всем страницам (header.php).
 * Содержит форму идентификации пользователя (email + пароль)
 * и ссылку на страницу регистрации.
 */

require_once __DIR__ . '/connect.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Лабораторная работа № 2') ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="index.php">
            <span class="logo-icon">🐘 PHP</span>
            <span>Лабораторная работа №&nbsp;2<br><small>Учётные записи пользователей</small></span>
        </a>

        <nav class="main-nav">
            <a href="index.php">Главная</a>
            <a href="register.php">Регистрация</a>
            <?php if (!empty($_SESSION['user'])): ?>
                <a href="edit.php?id=<?= (int)$_SESSION['user']['id'] ?>">Редактирование</a>
            <?php endif; ?>
        </nav>

        <?php if (empty($hideLogin) || $hideLogin !== true): ?>
        <?php if (!empty($_SESSION['user'])): ?>
            <!-- Пользователь уже идентифицирован -->
            <div class="userbox">
                <span class="username">👤 <?= e($_SESSION['user']['name']) ?>
                    <em class="role role-<?= e($_SESSION['user']['role']) ?>">
                        <?= $_SESSION['user']['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?>
                    </em>
                </span>
                <form action="login.php" method="post" class="inline-form">
                    <button type="submit" name="logout" value="1" class="btn btn-small">Выйти</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Форма идентификации (email + пароль), задание 3 -->
            <form class="login-form" action="login.php" method="post">
                <label>
                    <input type="email" name="email" placeholder="Email" required
                           value="<?= e($_POST['email'] ?? '') ?>">
                </label>
                <label>
                    <input type="password" name="password" placeholder="Пароль" required>
                </label>
                <button type="submit" class="btn">Войти</button>
                <a class="reg-link" href="register.php">Регистрация нового пользователя</a>
            </form>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</header>
<main class="container">
