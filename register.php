<?php
/**
 * Лабораторная работа № 2. Задание 4.
 * Страница регистрации нового пользователя.
 * Минимальное количество полей: email, пароль (и подтверждение), имя.
 * Обработчик — registerAction.php (задание 5).
 */

session_start();
require_once __DIR__ . '/connect.php';

$pageTitle = 'Регистрация — Лабораторная работа № 2';

// Список ошибок возвращается обработчиком через сессию
$errors = $_SESSION['reg_errors'] ?? [];
$old    = $_SESSION['reg_old'] ?? [];
unset($_SESSION['reg_errors'], $_SESSION['reg_old']);

require __DIR__ . '/header.php';
?>
<h1>Регистрация нового пользователя</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-err">
        <strong>Исправьте ошибки в форме:</strong>
        <ul>
            <?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <form action="registerAction.php" method="post" class="reg-form">
        <p class="field">
            <label for="email">Email <span class="req">*</span></label>
            <input type="email" id="email" name="email" required maxlength="190"
                   placeholder="user@example.com" value="<?= e($old['email'] ?? '') ?>">
        </p>
        <p class="field">
            <label for="password">Пароль <span class="req">*</span></label>
            <input type="password" id="password" name="password" required minlength="4" maxlength="64">
        </p>
        <p class="field">
            <label for="password2">Повторите пароль <span class="req">*</span></label>
            <input type="password" id="password2" name="password2" required minlength="4" maxlength="64">
        </p>
        <p class="field">
            <label for="name">Имя (ФИО) <span class="req">*</span></label>
            <input type="text" id="name" name="name" required maxlength="100"
                   placeholder="Иванов Иван Иванович" value="<?= e($old['name'] ?? '') ?>">
        </p>
        <p class="field">
            <label for="phone">Телефон (необязательно)</label>
            <input type="text" id="phone" name="phone" maxlength="30"
                   placeholder="+7-900-000-00-00" value="<?= e($old['phone'] ?? '') ?>">
        </p>

        <p class="hint">Роль «Пользователь» присваивается автоматически.
           Смена роли на «Администратор» выполняется только напрямую через БД.</p>

        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
        <a class="btn" href="index.php">Отмена</a>
    </form>
</div>

<?php require __DIR__ . '/footer.php'; ?>
