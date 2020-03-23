<?php
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '__include.php';

$host = "0.0.0.0:2604";

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($host, $loop);

$logger = function ($message) {
    echo sprintf("[%s]: %s\n", date('Y-m-d H:i:s'), $message);
};

$socket->on('connection', function (React\Socket\ConnectionInterface $client) use ($logger) {
    $client->write("Welcome");
    call_user_func($logger, sprintf("received connection from [%s]", $client->getRemoteAddress()));

    $client->on('data', function ($data) use ($client, $logger) {
        // remove any non-word characters (just for the demo)
        //$data = trim(preg_replace('/[^\w\d \.\,\-\!\?]/u', '', $data));
        $data = trim($data);

        // ignore empty messages
        if ($data === '') {
            return;
        }

        $start = strpos($data, "<SERVICE ID=\"") + 13;
        $end = strpos($data, "\"", $start);
        $reqSid = substr($data, $start, $end - $start);

        $start = strpos($data, "<TRACKID ID=\"") + 14;
        $end = strpos($data, "\"", $start);
        $reqTid = substr($data, $start, $end - $start);

        $start = strpos($data, "COMCODE=\"") + 9;
        $end = strpos($data, "\"", $start);
        $comCode = substr($data, $start, $end - $start);


        $reqTid .= "|$reqSid";

        $tid = date('Ymd') . "_" . sprintf("%07d", random_int(0, 100000));


        if (strpos($data, "TRACKID_GET") > 0) {
            call_user_func($logger, "[$reqTid] sending back get track id [$tid]");
            $client->write('<RESULT STCODE="0"><TRACKID ID="' . $tid . '"></TRACKID></RESULT>');
        } else if ($comCode === "AL_GET") {
            call_user_func($logger, "[$reqTid] sending back get get door access level");
            $client->write(file_get_contents('./storage/data/door_access_level.xml'));
        } else if ($comCode === "LAL_GET") {
            call_user_func($logger, "[$reqTid] sending back get get door access level");
            $client->write(file_get_contents('./storage/data/lift_door_access_level.xml'));
        } else if ($comCode === "AG_GET") {
            call_user_func($logger, "[$reqTid] sending back get get access groups");
            $client->write(file_get_contents('./storage/data/access_groups.xml'));
        } else if (in_array($comCode, ["STAFF_ADD1", "STAFF_MOD", "STAFF_DEL", "CARD_ADD", "CARD_MOD", "CARD_DEL", "CARD_ACT", "CARD_DCT"])) {
            call_user_func($logger, "[$reqTid] sending back [$comCode] response");
            $client->write(file_get_contents('./storage/data/action_response.xml'));
        } else {
            call_user_func($logger, $data);
            call_user_func($logger, "[$reqTid] sending error response");
            $client->write(file_get_contents('./storage/data/error_response.xml'));
        }
    });
});

$loop->run();
