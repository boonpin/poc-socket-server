<?php
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '__include.php';

$host = "10.8.0.1";
$port = 2604;

echo "connecting to $host:$port ......\n";

$loop = React\EventLoop\Factory::create();
$connector = new React\Socket\Connector($loop, ['tcp']);
$connector->connect("$host:$port")->then(function (React\Socket\ConnectionInterface $connection) use (&$eventCodes, $loop) {
    $timer = $this->loop->addPeriodicTimer(30, function () use ($connection, &$eventCodes) {
        echo "sending data ......\n";
        $xml = file_get_contents(dirname(__FILE__) . "/../storage/data/sample_entrypass_event.xml");
        // print_r($xml);

        shuffle($eventCodes);
        $xml = str_replace('##TRCODE##', $eventCodes[0]['code'], $xml);

        $xml = str_replace('##ETYPE##', random_int(0, 2), $xml);
        $xml = str_replace('##TRDATE##', date('Ymd'), $xml);
        $xml = str_replace('##TRDATE##', date('His'), $xml);
        $xml = str_replace('##CARDNO##', sprintf("%08d", random_int(1, 9999999)), $xml);
        $xml = str_replace('##STAFFNO##', sprintf("%08d", random_int(1, 1000)), $xml);

        $connection->write($xml);
    });

    $connection->on('data', function ($data) {
        echo "[RECEIVING]\n";
        foreach (explode("\n", $data) as $line) {
            echo "\t$line\n";
        }
    });
    $connection->on('close', function () use ($timer) {
        echo "[CLOSE]\n";
        $this->close();
        $this->loop->cancelTimer($timer);
    });
}, function (Exception $e) {
    print_r($e);
});
$loop->run();