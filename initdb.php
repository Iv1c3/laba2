<?php
/**
 * Лабораторная работа № 2. Задание 5 (подготовка окружения).
 * Утилита создания таблицы users и тестовых записей для обеих СУБД.
 *
 * Запуск в браузере:  http://localhost/laba2/initdb.php
 * Запуск из консоли:  php initdb.php mysql   или   php initdb.php sqlite
 */

declare(strict_types=1);

$driver = 'sqlite';
if (isset($_GET['driver'])) {
    $driver = $_GET['driver'] === 'mysql' ? 'mysql' : 'sqlite';
} elseif (PHP_SAPI === 'cli' && isset($argv[1])) {
    $driver = $argv[1] === 'mysql' ? 'mysql' : 'sqlite';
}

define('DB_DRIVER', $driver);
require __DIR__ . '/connect.php';

$pdo   = getDb();
$table = usersTable();

/* ---- SQL-определения, зависящие от СУБД ---------------------------------- */
$q = ($driver === 'mysql') ? '`' : '"'; // символы цитирования имён в MySQL / SQLite
$T = $q . $table . $q;

if ($driver === 'mysql') {
    $ddl = "CREATE TABLE IF NOT EXISTS $T (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        email      VARCHAR(190) NOT NULL UNIQUE,
        password   CHAR(32)     NOT NULL,
        name       VARCHAR(100) NOT NULL,
        phone      VARCHAR(30)  DEFAULT NULL,
        role       ENUM('user','admin') NOT NULL DEFAULT 'user',
        reg_date   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
} else {
    $ddl = "CREATE TABLE IF NOT EXISTS $T (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        email      TEXT NOT NULL UNIQUE,
        password   TEXT NOT NULL,
        name       TEXT NOT NULL,
        phone      TEXT,
        role       TEXT NOT NULL DEFAULT 'user' CHECK (role IN ('user','admin')),
        reg_date   TEXT NOT NULL DEFAULT (datetime('now','localtime'))
    )";
}

$log = [];
try {
    $pdo->exec($ddl);
    $log[] = "[$driver] Таблица \"$table\" создана (или уже существовала).";

    // Тестовые записи (задание 5: «добавив несколько записей в БД»)
    $seed = [
        ['admin@example.com', 'admin123', 'Администратор Системы', '+7-000-000-00-00', 'admin'],
        ['ivanov@example.com', 'test1234', 'Иванов Иван Иванович',  '+7-900-111-22-33', 'user'],
        ['petrov@example.com', 'pass5678', 'Петров Пётр Петрович',  '+7-900-444-55-66', 'user'],
    ];

    $cnt = 0;
    $sel = $pdo->prepare("SELECT id FROM $T WHERE email = ?");
    $ins = $pdo->prepare(
        "INSERT INTO $T (email, password, name, phone, role, reg_date)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    foreach ($seed as [$email, $pass, $name, $phone, $role]) {
        $sel->execute([$email]);
        if ($sel->fetch()) {
            continue; // запись уже есть — не дублируем
        }
        $ins->execute([$email, hashPassword($pass), $name, $phone, $role,
                       date('Y-m-d H:i:s')]);
        $cnt++;
    }
    $log[] = "[$driver] Добавлено тестовых записей: $cnt.";

    // Проверка результата
    $rows = $pdo->query("SELECT id, email, name, role, reg_date FROM $T ORDER BY id")->fetchAll();
} catch (PDOException $e) {
    $log[] = 'ОШИБКА: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Инициализация БД (<?= e($driver) ?>)</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f5f6fa; margin: 2rem; }
        table { border-collapse: collapse; margin-top: 1rem; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 6px 12px; font-size: 14px; }
        th { background: #306998; color: #fff; }
        li { margin-bottom: .3rem; }
    </style>
</head>
<body>
<h1>Инициализация базы данных (<?= e(strtoupper($driver)) ?>)</h1>
<ul><?php foreach ($log as $l) echo '<li>' . e($l) . '</li>'; ?></ul>

<?php if (!empty($rows)): ?>
    <h2>Содержимое таблицы «<?= e($table) ?>»</h2>
    <table>
        <tr><th>ID</th><th>Email</th><th>Имя</th><th>Роль</th><th>Дата регистрации</th></tr>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= e((string)$r['id']) ?></td>
            <td><?= e($r['email']) ?></td>
            <td><?= e($r['name']) ?></td>
            <td><?= e($r['role']) ?></td>
            <td><?= e($r['reg_date']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="index.php">Перейти на главную страницу</a></p>
<?php endif; ?>
</body>
</html>
