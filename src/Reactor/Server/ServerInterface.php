<?php
namespace Voidcontext\Arc\Reactor\Server;

interface ServerInterface
{
    /**
     * Attaches a request handler
     *
     * @param callable $handler
     */
    public function attachRequestHandler(callable $handler);

    /**
     * Starts the server
     *
     * @param $port
     */
    public function listen($port);
}