<?php
namespace Voidcontext\Arc\Reactor\Server\Adapter;

use \Evenement\EventEmitter;
use \Voidcontext\Arc\Reactor\Server\ServerInterface;

/**
 * Class DummyServer
 *
 * DummyServer implements the Server Interface, but does nothing.
 * A request event can be triggered on this server,
 * which will call the request handler.
 * This class is mainly for test purposes.
 *
 * @package Voidcontext\Arc\Reactor\Server\Adapter
 */
class DummyServer extends EventEmitter implements ServerInterface
{
    /** @var callable */
    protected $requestHandler;

    /** @var int */
    public $port;

    /**
     * Sets the internal request handler
     *
     * @param callable $requestHandler
     */
    public function attachRequestHandler(callable $requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }

    /**
     * Starts listening to the request event
     *
     * @param $port
     */
    public function listen($port)
    {
        $this->on('request', $this->requestHandler);

        $this->port = $port;
    }
}
