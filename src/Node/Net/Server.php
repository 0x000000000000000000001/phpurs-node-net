<?php

$exports = [];

// Event-only TCP server placeholder. Listening emits events without opening
// a socket; HTTP servers delegate through their httpListen callback.
class PhpursMockNetServer
{
    public $handlers = [];
    public $any = null;

    public function on($event, $cb)
    {
        $key =
            is_string($event) && strpos($event, 'Symbol(') === 0 ? substr($event, 7, -1) : $event;
        $this->handlers[$key][] = $cb;
        return $this;
    }

    public function emit($event, ...$args)
    {
        foreach ($this->handlers[$event] ?? [] as $cb) {
            $cb(...$args);
        }
        return true;
    }
}

$exports['newServerImpl'] = function (...$args) {
    return new PhpursMockNetServer();
};
$exports['newServerOptionsImpl'] = function (...$args) {
    return new PhpursMockNetServer();
};

$exports['listenImpl'] = function ($server, $options) {
    if (is_object($server) && isset($server->httpListen) && is_callable($server->httpListen)) {
        ($server->httpListen)($options);
        return;
    }
    if ($server instanceof PhpursMockNetServer) {
        $server->any = (object) ['port' => 0, 'host' => 'localhost'];
        if (class_exists('\\Revolt\\EventLoop')) {
            \Revolt\EventLoop::queue(function () use ($server) {
                $server->emit('listening');
            });
        } else {
            $server->emit('listening');
        }
        return;
    }
    if (isset($server->requestListener)) {
        $logger = new \Psr\Log\NullLogger();
        $serverHandler = \Amp\Http\Server\SocketHttpServer::createForDirectAccess($logger);
        $host = $options->host ?? '0.0.0.0';
        $port = $options->port ?? 80;
        $serverHandler->expose($host . ':' . $port);
        $server->amphpServer = $serverHandler;
    }
};

$exports['closeImpl'] = function ($server) {
    if (is_object($server) && isset($server->httpListen) && method_exists($server, 'close')) {
        $server->close();
        return;
    }
    if ($server instanceof PhpursMockNetServer) {
        $server->emit('close');
        return;
    }
    if (isset($server->amphpServer)) {
        $server->amphpServer->stop();
    }
};

$exports['addressTcpImpl'] = function ($server) {
    return null;
};
$exports['addressIpcImpl'] = function ($server) {
    return null;
};
$exports['getConnectionsImpl'] = function ($server, $cb) {
    return null;
};
$exports['listeningImpl'] = function ($server) {
    return true;
};
$exports['maxConnectionsImpl'] = function ($server) {
    return 0;
};
$exports['refImpl'] = function ($server) {
    return null;
};
$exports['unrefImpl'] = function ($server) {
    return null;
};

return $exports;
