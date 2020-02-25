<?php

class PlainTextSocketServer
{
    private $host;

    private $loop;

    private $socket;

    private $logger;

    public function __construct($host)
    {
        $this->host = $host;
        $this->loop = React\EventLoop\Factory::create();
        $this->socket = new React\Socket\Server($host, $this->loop);

        $this->initHandler();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message, $level = "info")
    {
        if (is_callable($this->logger)) {
            call_user_func($this->logger, $message, $level);
        }
    }

    private function initHandler()
    {
        $this->socket->on('connection', function (React\Socket\ConnectionInterface $client) {
            $this->log(sprintf("received connection from [%s]", $client->getRemoteAddress()));

            $client->on('data', function ($data) use ($client) {
                // remove any non-word characters (just for the demo)
                //$data = trim(preg_replace('/[^\w\d \.\,\-\!\?]/u', '', $data));
                $data = trim($data);

                // ignore empty messages
                if ($data === '') {
                    return;
                }

                if ($data === 'exit') {
                    $this->log("client sent exit command");
                    $client->write("bye bye");
                    $client->close();
                }

                $this->log($data);
            });
        });
    }

    public function run()
    {
        $this->log("starting socket server on " . $this->host);
        $this->loop->run();
    }

    public function close()
    {
        $this->socket->close();
        $this->loop->stop();
    }
}