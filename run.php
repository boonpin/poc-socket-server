<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '__include.php';


$server = new PlainTextSocketServer("127.0.0.1:1234");

$server->setLogger(function ($message, $level) {
    echo sprintf("[%s]: $message\n",
        date('Y-m-d H:i:s')
    );
});
$server->run();