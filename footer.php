<?php
/**
 * Лабораторная работа № 2. Задание 3.
 * Подвал (footer) сайта — подключается ко всем страницам.
 */
?>
</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> Лабораторная работа № 2. Веб-программирование.</span>
        <span class="footer-meta">
            СУБД: <?= strtoupper(DB_DRIVER) ?> &nbsp;|&nbsp;
            PHP <?= e(PHP_VERSION) ?> &nbsp;|&nbsp;
            Страница сгенерирована за <?= round(microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true)), 4) ?> с
        </span>
    </div>
</footer>
</body>
</html>
