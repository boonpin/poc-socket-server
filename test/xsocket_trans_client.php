<?php
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '__include.php';

$host = "127.0.0.1";
$port = 2605;

echo "connecting to $host:$port ......\n";

function createEvent($file)
{
    $xml = file_get_contents(dirname(__FILE__) . "/../storage/data/$file");
    $xml = str_replace('##TRDATE##', date('Ymd'), $xml);
    $xml = str_replace('##TRDATE##', date('His'), $xml);
    return $xml;
}

function createAccessCardDoorEntry($cardNo, $staffNo, $staffName, $department)
{
    $transactions = [];

    $e1 = createEvent("door_event.xml");
    $e1 = str_replace('##TRCODE##', "Ca", $e1);
    $e1 = str_replace('##TRDESC##', "Valid Card Entry", $e1);
    $e1 = str_replace('##CARDNO##', $cardNo, $e1);
    $e1 = str_replace('##STAFFNO##', $staffNo, $e1);
    $e1 = str_replace('##STAFFNAME##', $staffName, $e1);
    $e1 = str_replace('##DEPTNAME##', $department, $e1);
    $transactions[] = $e1;

    $e2 = createEvent("door_event.xml");
    $e2 = str_replace('##TRCODE##', "Dc", $e2);
    $e2 = str_replace('##TRDESC##', "Door is opened", $e2);
    $e2 = str_replace('##CARDNO##', $cardNo, $e2);
    $e2 = str_replace('##STAFFNO##', $staffNo, $e2);
    $e2 = str_replace('##STAFFNAME##', $staffName, $e2);
    $e2 = str_replace('##DEPTNAME##', $department, $e2);
    $transactions[] = $e2;

    $e3 = createEvent("door_event.xml");
    $e3 = str_replace('##TRCODE##', "Dg", $e3);
    $e3 = str_replace('##TRDESC##', "Door is closed", $e3);
    $e3 = str_replace('##CARDNO##', $cardNo, $e3);
    $e3 = str_replace('##STAFFNO##', $staffNo, $e3);
    $e3 = str_replace('##STAFFNAME##', $staffName, $e3);
    $e3 = str_replace('##DEPTNAME##', $department, $e3);
    $transactions[] = $e3;

    return $transactions;
}


$loop = React\EventLoop\Factory::create();
$connector = new React\Socket\Connector($loop, ['tcp']);
$connector->connect("$host:$port")->then(function (React\Socket\ConnectionInterface $connection) use ($loop) {
    echo "connected\n";
    $timer = $loop->addPeriodicTimer(5, function () use ($connection) {
        echo "not doing anything .....\n";

        switch (random_int(0, 1)) {
            case 0:
                echo "sending access card door entry ......\n";
                $transactions = createAccessCardDoorEntry(
                    sprintf("%08d", random_int(1, 9999999)),
                    sprintf("%08d", random_int(1, 1000)),
                    "Staff Name " . random_int(1, 9999),
                    "RND"
                );
                foreach ($transactions as $trans) {
                    $connection->write($trans);
                    usleep(500);
                }
                $transactions = null;
                break;
            default:
                echo "not doing anything .....\n";
        }
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