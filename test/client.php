<?php
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '__include.php';

$host = "127.0.0.1";
$port = 1234;

$loop = React\EventLoop\Factory::create();
$connector = new React\Socket\Connector($loop, ['tcp']);
$connector->connect("$host:$port")->then(function (React\Socket\ConnectionInterface $connection) use (&$eventCodes, $loop) {
    $connection->write(file_get_contents(__DIR__ . "/../storage/data/very_long_data.xml"));
}, function (Exception $e) {
    print_r($e);
});
$loop->run();