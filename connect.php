<?php
/**
 * Лабораторная работа № 2. Задания 2–5.
 * Общий модуль подключения к базе данных (PDO) и вспомогательные функции.
 *
 * Подключение активной СУБД задаётся константой DB_DRIVER в settings.php:
 *   'mysql'  — используется конфигурация configM.php
 *   'sqlite' — используется конфигурация configS.php
 */

declare(strict_types=1);

// ---- Настройки проекта ----------------------------------------------------
if (!defined('DB_DRIVER')) {
    // По умолчанию SQLite — чтобы проект работал на OpenServer сразу,
    // без предварительного создания базы MySQL.
    define('DB_DRIVER', 'sqlite');
}

/**
 * Возвращает объект PDO по активной конфигурации (configM или configS).
 * Соединение создаётся один раз (паттерн «синглтон»).
 */
function getDb(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $configFile = (DB_DRIVER === 'mysql') ? 'configM.php' : 'configS.php';
    $config     = require __DIR__ . '/' . $configFile;

    try {
        if ($config['driver'] === 'sqlite') {
            // Каталог для файла БД создаём автоматически
            $dir = dirname($config['dbname']);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $pdo = new PDO($config['dsn'], null, null, $config['options']);
            $pdo->exec('PRAGMA foreign_keys = ON');
        } else {
            $pdo = new PDO(
                $config['dsn'],
                $config['user'],
                $config['password'],
                $config['options']
            );
        }
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Ошибка подключения к базе данных (' . htmlspecialchars(DB_DRIVER) . '): '
             . $e->getMessage());
    }

    return $pdo;
}

/**
 * Имя таблицы пользователей из активного конфига.
 */
function usersTable(): string
{
    $configFile = (DB_DRIVER === 'mysql') ? 'configM.php' : 'configS.php';
    $config     = require __DIR__ . '/' . $configFile;
    return $config['table'];
}

/**
 * Двойное хеширование пароля (задание 5).
 * Схема: MD5( SHA1( пароль ) ).
 * Оба алгоритма вызваны через built-in функции PHP 8 (ext hash).
 */
function hashPassword(string $plain): string
{
    return md5(sha1($plain));
}

/**
 * Экранирование вывода для HTML (короткая обёртка).
 */
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/*
 * Совместимость с серверами, где расширение mbstring не включено.
 * Если mb_* недоступны — используются безопасные аналоги для UTF-8.
 */
if (!function_exists('mb_strtolower')) {
    function mb_strtolower(string $s): string { return strtolower($s); }
}
if (!function_exists('mb_strlen')) {
    // Подсчёт символов UTF-8 без mbstring
    function mb_strlen(string $s): int
    {
        $len = 0;
        for ($i = 0, $n = strlen($s); $i < $n; $i++) {
            $c = ord($s[$i]);
            if (($c & 0xC0) !== 0x80) {
                $len++; // начало последовательности байт
            }
        }
        return $len;
    }
}
