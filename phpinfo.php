<?php
/**
 * Лабораторная работа № 2. Задание 1.
 * Страница «phpinfo()» — информация о текущей конфигурации PHP.
 *
 * ВНИМАНИЕ: на боевом хостинге такие страницы закрывают; для учебной
 * работы на локальном сервере OpenServer это допустимо.
 */

// Разрешаем вывод phpinfo и показываем полную информацию
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Небольшая шапка над стандартным выводом phpinfo()
echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8">';
echo '<title>Конфигурация PHP — phpinfo()</title></head><body>';
echo '<h1 style="font-family:Tahoma;text-align:center;">'
   . 'Информация о текущей конфигурации PHP (задание 1)<br>'
   . '<small>Сервер: ' . htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'CLI')
   . ' | Версия PHP: ' . PHP_VERSION . '</small></h1>';

phpinfo(INFO_ALL);

echo '</body></html>';
