<?php
namespace Voidcontext\Arc\Reactor\Server\Adapter;

use \React\EventLoop\LoopInterface;
use \React\Http\Server as HttpServer;
use \React\Socket\Server as SocketServer;
use \Voidcontext\Arc\Reactor\Server\ServerInterface;

/**
 * Class ReactHttpServer
 *
 * ReactHttpServer implements the ServerInterface using ReactPHP's
 * HttpServer and Socket class
 *
 * @package Voidcontext\Arc\Reactor\Server\Adapter
 */
class ReactHttpServer implements ServerInterface
{
    /** @var LoopInterface  */
    protected $loop;

    /** @var callable */
    protected $requestHandler;

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * Sets the request handler
     * @param callable $handler
     */
    public function attachRequestHandler(callable $handler)
    {
        $this->requestHandler = $handler;
    }

    /**
     * Creates a new \React\Http\Server and runs it
     *
     * @param int $port
     */
    public function listen($port)
    {
        $socket = new SocketServer($this->loop);
        $server = new HttpServer($socket);

        $server->on('request', $this->requestHandler);
        $socket->listen($port, '0.0.0.0');
    }
}
