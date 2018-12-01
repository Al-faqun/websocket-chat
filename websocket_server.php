<?php
require __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('Europe/Moscow');

try {
    $app = \webs_chat\Application::Instance();
    $app->start();
} catch (Throwable $exeption) {
    echo 'Caught exception: ';
    var_dump($exeption->getMessage());
}