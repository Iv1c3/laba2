<?php
/**
 * Лабораторная работа № 2. Задание 6.
 * Форма редактирования данных зарегистрированного пользователя.
 *
 * Доступ к странице:
 *  - авторизованный пользователь может редактировать СВОЮ запись;
 *  - пользователь с ролью «Администратор» может редактировать ЛЮБУЮ запись,
 *    для него дополнительно выводится список всех пользователей.
 *
 * Редактируются все поля, КРОМЕ email и даты регистрации (задание 6).
 * Обработчик — editAction.php.
 */

session_start();
require_once __DIR__ . '/connect.php';

$pageTitle = 'Редактирование пользователя — Лабораторная работа № 2';

if (empty($_SESSION['user'])) {
    // Неавторизованным доступ закрыт
    $hideLogin = false;
    require __DIR__ . '/header.php';
    echo '<div class="alert alert-err">Для редактирования данных необходимо войти в систему.</div>';
    require __DIR__ . '/footer.php';
    exit;
}

$current = $_SESSION['user'];                 // данные вошедшего пользователя
$isAdmin = ($current['role'] === 'admin');

$T = (DB_DRIVER === 'mysql' ? '`' : '"') . usersTable() . (DB_DRIVER === 'mysql' ? '`' : '"');

// Определяем, какую запись редактируем
$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$current['id'];

$stmt = getDb()->prepare("SELECT id, email, name, phone, role, reg_date FROM $T WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $errors = ['Запись с ID ' . $id . ' не найдена в базе данных.'];
} elseif (!$isAdmin && (int)$user['id'] !== (int)$current['id']) {
    $errors = ['Редактирование чужой записи запрещено.'];
} else {
    $errors = [];
}

// Список пользователей для администратора
$users = [];
if ($isAdmin) {
    $users = getDb()->query("SELECT id, email, name, role FROM $T ORDER BY id")->fetchAll();
}

$errors = array_merge($errors, $_SESSION['edit_errors'] ?? []);
unset($_SESSION['edit_errors']);

require __DIR__ . '/header.php';
?>
<h1>Редактирование данных пользователя</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-err">
        <ul><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul>
    </div>
<?php endif; ?>

<?php if ($isAdmin && !empty($users)): ?>
<div class="card" style="margin-bottom:18px;">
    <h2>Выбор записи (режим администратора)</h2>
    <table class="listing">
        <tr><th>ID</th><th>Email</th><th>Имя</th><th>Роль</th><th></th></tr>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['name']) ?></td>
            <td><span class="role role-<?= e($u['role']) ?>"><?= e($u['role']) ?></span></td>
            <td><a href="edit.php?id=<?= (int)$u['id'] ?>">Редактировать</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>

<?php if ($user): ?>
<div class="card">
    <form action="editAction.php" method="post">
        <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

        <p class="field">
            <label for="email">Email (изменению не подлежит)</label>
            <input type="text" id="email" value="<?= e($user['email']) ?>" class="locked" disabled>
        </p>

        <p class="field">
            <label for="reg_date">Дата регистрации (изменению не подлежит)</label>
            <input type="text" id="reg_date" value="<?= e($user['reg_date']) ?>" class="locked" disabled>
        </p>

        <p class="field">
            <label for="name">Имя (ФИО) <span class="req">*</span></label>
            <input type="text" id="name" name="name" required minlength="3" maxlength="100"
                   value="<?= e($user['name']) ?>">
        </p>

        <p class="field">
            <label for="phone">Телефон</label>
            <input type="text" id="phone" name="phone" maxlength="30" value="<?= e($user['phone']) ?>">
        </p>

        <p class="field">
            <label for="password">Новый пароль (оставьте пустым, чтобы не менять)</label>
            <input type="password" id="password" name="password" minlength="4" maxlength="64">
        </p>

        <p class="field">
            <label for="password2">Повтор нового пароля</label>
            <input type="password" id="password2" name="password2" minlength="4" maxlength="64">
        </p>

        <?php if ($isAdmin): ?>
        <!-- Смена роли доступна только администратору -->
        <p class="field">
            <label for="role">Роль</label>
            <select id="role" name="role">
                <option value="user"  <?= $user['role'] === 'user'  ? 'selected' : '' ?>>Пользователь</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
            </select>
        </p>
        <?php else: ?>
        <p class="field">
            <label>Роль</label>
            <input type="text" class="locked" disabled
                   value="<?= $user['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?>">
        </p>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
        <a class="btn" href="index.php">Отмена</a>
    </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
