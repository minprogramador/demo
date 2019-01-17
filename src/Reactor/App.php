<?php
namespace Voidcontext\Arc\Reactor;

use \Closure;
use \Exception;
use \Evenement\EventEmitter;
use \FastRoute\Dispatcher;
use \FastRoute\RouteCollector;
use \React\Http\Response;
use \React\Http\Request;
use \Voidcontext\Arc\Reactor\Server\ServerInterface;

/**
 * Lightweight web application
 *
 * @package Voidcontext\Arc\Reactor
 * @author Gabor Pihaj
 */
class App extends EventEmitter
{
    /** @var array Config array */
    protected $config = [
        'port' => 1337,
    ];

    /** @var array Registered routes */
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    /** @var Closure[] */
    protected $middlewares = [];

    /** @var  ServerInterface */
    protected $server;

    /**
     * @param ServerInterface $server
     * @param array $config
     */
    public function __construct(ServerInterface $server, $config = [])
    {
        $this->server = $server;
        $this->setConfig($config);
    }

    /**
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        if (in_array(strtoupper($name), array_keys($this->routes))) {
            // When the called function is one of the verbs,
            // then we register the given handler
            list($route, $handler) = $arguments;
            $this->routes[strtoupper($name)][$route] = $handler;
        }
    }

    /**
     * Registers a middleware
     *
     * @param Closure $middleware
     */
    public function middleware(Closure $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Sets config parameters from the given array
     *
     * @param array $config
     */
    public function setConfig(array $config)
    {
        foreach ($config as $key => $value) {
            if (array_key_exists($key, $this->config)) {
                $this->config[$key] = $value;
            }
        }
    }

    /**
     * Creates a FastRoute dispatcher and adds previously registered routes
     *
     * @return mixed Dispatcher
     */
    protected function createDispatcher()
    {
        $routes = $this->routes;
        return \FastRoute\simpleDispatcher(function (RouteCollector $r) use ($routes) {
            foreach ($routes as $verb => $addedRoutes) {
                foreach ($addedRoutes as $route => $handler) {
                    $r->addRoute($verb, $route, $handler);
                }
            }
        });
    }

    /**
     * @param Closure[] $handlers
     * @param Request $request
     * @param Response $response
     */
    protected function runNext(array $handlers, Request $request, Response $response)
    {
        $handler = array_shift($handlers);

        $handler($request, $response, function (Exception $e = null) use ($handlers, $request, $response) {
            if($e !== null) {
                $this->emit('error', [$e, $request, $response]);
            } elseif ($handlers) {
                $this->runNext($handlers, $request, $response);
            } else {
                // @TODO: what should we do, when there isn't more handler, but we reached the end of the chain?

                // Terminate the response stream, just for to be sure it isn't using rou resources anymore
                $response->end();
            }
        });
    }

    /**
     * Creates a request handler
     *
     * @param Dispatcher $dispatcher
     * @return callable
     */
    protected function requestHandler(Dispatcher $dispatcher)
    {
        return function (Request $req, Response $res) use ($dispatcher) {
            try {
                $result = $dispatcher->dispatch($req->getMethod(), $req->getPath());
                switch ($result[0]) {
                    case Dispatcher::FOUND:
                        // @todo param handling -> result 2
                        $this->runNext(
                            array_merge($this->middlewares, (array)$result[1]),
                            $req,
                            $res
                        );
                        break;
                    case Dispatcher::METHOD_NOT_ALLOWED:
                    case Dispatcher::NOT_FOUND:
                        $res->writeHead(404);
                        $res->end();
                        break;

                }
            } catch (\Exception $e) {
                $this->emit('error', [$e, $req, $res]);
            }
        };
    }

    /**
     * Starts the HTTP server
     */
    public function run()
    {
        $dispatcher = $this->createDispatcher();
        $this->server->attachRequestHandler($this->requestHandler($dispatcher));
        $this->server->listen($this->config['port'], '0.0.0.0');
    }
}
