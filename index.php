<?php
/**
 * Лабораторная работа № 2. Задание 3.
 * Главная страница сайта: форма идентификации (в шапке) + ссылка на регистрацию.
 */

session_start();
require_once __DIR__ . '/connect.php';

$pageTitle = 'Главная — Лабораторная работа № 2';

// Сообщение после возврата со страницы регистрации
$flash = $_GET['msg'] ?? '';

require __DIR__ . '/header.php';
?>
<h1>Добро пожаловать!</h1>

<?php if ($flash === 'registered'): ?>
    <div class="alert alert-ok">Регистрация успешна. Теперь вы можете войти по своему email и паролю.</div>
<?php elseif ($flash === 'updated'): ?>
    <div class="alert alert-ok">Данные пользователя успешно обновлены.</div>
<?php endif; ?>

<?php if (!empty($_SESSION['login_error'])): ?>
    <div class="alert alert-err"><?= e($_SESSION['login_error']) ?></div>
    <?php unset($_SESSION['login_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['user'])): ?>
    <div class="card">
        <h2>Вы вошли в систему</h2>
        <table class="profile">
            <tr><th>ID</th>              <td><?= (int)$_SESSION['user']['id'] ?></td></tr>
            <tr><th>Email</th>           <td><?= e($_SESSION['user']['email']) ?></td></tr>
            <tr><th>Имя</th>             <td><?= e($_SESSION['user']['name']) ?></td></tr>
            <tr><th>Телефон</th>         <td><?= e($_SESSION['user']['phone']) ?></td></tr>
            <tr><th>Роль</th>            <td><?= $_SESSION['user']['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?></td></tr>
            <tr><th>Дата регистрации</th><td><?= e($_SESSION['user']['reg_date']) ?></td></tr>
        </table>
        <p><a class="btn" href="edit.php?id=<?= (int)$_SESSION['user']['id'] ?>">Редактировать данные</a></p>
    </div>
<?php else: ?>
    <div class="card">
        <h2>Вход ещё не выполнен</h2>
        <p>Используйте форму <strong>«Войти»</strong> в шапке страницы для идентификации
           по email и паролю, либо создайте новую учётную запись:</p>
        <p>
            <a class="btn btn-primary" href="register.php">Зарегистрироваться</a>
        </p>
        <p class="hint">Подсказка для проверки работы модулей (тестовые записи из initdb.php):
            admin@example.com / admin123 — роль «Администратор»;
            ivanov@example.com / test1234 — роль «Пользователь».</p>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
