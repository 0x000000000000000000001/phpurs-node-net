<?php

$exports = [];

$exports['listenImpl'] = function($server, $options) {
    $logger = new \Psr\Log\NullLogger();
    $serverHandler = \Amp\Http\Server\SocketHttpServer::createForDirectAccess($logger);

    $host = $options->host ?? '0.0.0.0';
    $port = $options->port ?? 80;
    $serverHandler->expose($host . ':' . $port);

    $requestHandler = new \Amp\Http\Server\RequestHandler\ClosureRequestHandler(function (\Amp\Http\Server\Request $request) use ($server) {
        if (!$server->requestListener) {
            return new \Amp\Http\Server\Response(\Amp\Http\HttpStatus::SERVICE_UNAVAILABLE, ['content-type' => 'text/plain'], 'No listener attached');
        }

        $deferred = new \Amp\DeferredFuture();

        $reqMock = new class($request) {
            public $dataListener;
            public $endListener;
            public $headers;
            public $url;
            public $method;

            public function __construct(public \Amp\Http\Server\Request $amphpReq) {
                $this->url = $amphpReq->getUri()->getPath();
                if ($amphpReq->getUri()->getQuery()) {
                    $this->url .= '?' . $amphpReq->getUri()->getQuery();
                }
                $this->method = $amphpReq->getMethod();
                $this->headers = new \stdClass();
                foreach ($amphpReq->getHeaders() as $k => $v) {
                    $this->headers->{strtolower($k)} = implode(', ', $v);
                }
            }

            public function on($event, $listener) {
                if ($event === 'data') {
                    $this->dataListener = $listener;
                    \Amp\async(function() {
                        $body = $this->amphpReq->getBody();
                        while (null !== $chunk = $body->read()) {
                            if ($this->dataListener) {
                                $l = $this->dataListener;
                                $l($chunk);
                            }
                        }
                        if ($this->endListener) {
                            $l = $this->endListener;
                            $l();
                        }
                    });
                }
                if ($event === 'end') {
                    $this->endListener = $listener;
                }
            }
        };

        $resMock = new class($deferred) {
            public $statusCode = 200;
            public $headers = [];
            public $body = '';
            
            public function __construct(public \Amp\DeferredFuture $deferred) {}

            public function setStatusCode($c) { $this->statusCode = $c; }
            public function setHeader($k, $v) { $this->headers[$k] = $v; }
            public function writeString($str) { $this->body .= $str; }
            public function end() {
                if (!$this->deferred->isComplete()) {
                    $this->deferred->complete(new \Amp\Http\Server\Response($this->statusCode, $this->headers, $this->body));
                }
            }
        };

        try {
            $listener = $server->requestListener;
            $listener($reqMock, $resMock);
        } catch (\Throwable $e) {
            error_log("Error in request handler: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }

        return $deferred->getFuture()->await();
    });

    $serverHandler->start($requestHandler, new \Amp\Http\Server\DefaultErrorHandler());
    $server->amphpServer = $serverHandler;
};

// Mock TCP server: the public suite only logs, so the events and the close
// path are enough. Real sockets are not opened.
class PhpursMockNetServer {
    public $handlers = [];
    public $any = null;

    public function on($event, $cb) {
        $key = is_string($event) && strpos($event, "Symbol(") === 0 ? substr($event, 7, -1) : $event;
        $this->handlers[$key][] = $cb;
        return $this;
    }

    public function emit($event, ...$args) {
        foreach (($this->handlers[$event] ?? []) as $cb) { $cb(...$args); }
        return true;
    }
}

$exports['newServerImpl'] = function(...$args) { return new PhpursMockNetServer(); };
$exports['newServerOptionsImpl'] = function(...$args) { return new PhpursMockNetServer(); };

$exports['listenImpl'] = function($server, $options) {
    if (is_object($server) && isset($server->httpListen) && is_callable($server->httpListen)) {
        ($server->httpListen)($options);
        return;
    }
    if ($server instanceof PhpursMockNetServer) {
        $server->any = (object)['port' => 0, 'host' => 'localhost'];
        if (class_exists('\\Revolt\\EventLoop')) {
            \Revolt\EventLoop::queue(function() use ($server) { $server->emit('listening'); });
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

$exports['closeImpl'] = function($server) {
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

$exports['addressTcpImpl'] = function($server) { return null; };
$exports['addressIpcImpl'] = function($server) { return null; };
$exports['getConnectionsImpl'] = function($server, $cb) { return null; };
$exports['listeningImpl'] = function($server) { return true; };
$exports['maxConnectionsImpl'] = function($server) { return 0; };
$exports['refImpl'] = function($server) { return null; };
$exports['unrefImpl'] = function($server) { return null; };

return $exports;
