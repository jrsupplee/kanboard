<?php

require_once 'vendor/autoload.php';
global $twig;

$loader = new \Twig\Loader\FilesystemLoader('app/Template');
$twig = new \Twig\Environment($loader, [
    'cache' => 'template_cache',
]);

use Kanboard\Core\Controller\Runner;

try {
    require __DIR__.'/app/common.php';
    $container['router']->dispatch();
    $runner = new Runner($container);
    $runner->execute();
} catch (Exception $e) {
    echo 'Internal Error: '.$e->getMessage();
}
