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
            public $dataListener = null;
            public $endListener = null;
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

$exports['closeImpl'] = function($server) {
    if (isset($server->amphpServer)) {
        $server->amphpServer->stop();
    }
};

return $exports;
