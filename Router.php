<?php

namespace app\Core;

use app\core\Application;
use app\core\Response;
use Core\Exceptions\NotFoundException;

class Router
{
    public Request $request;
    protected array $routes = [];

    public Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->method();
        $callback = $this->routes[$method][$path] ?? false;
        if ($callback === false) {
            throw new NotFoundException();
        }
        if (is_string($callback)) {
            return $this->renderView($callback);
        }
        if (is_array($callback)) {
            /** @var Controller $controller */
            $controller= new $callback[0]();
            Application::$app->controller = $controller;
            $controller->action = $callback[1];
            foreach($controller->getMiddleware() as $middleware) {$middleware->execute();}
            $callback[0] = $controller;
        }
        return call_user_func($callback, $this->request, $this->response);
    }

    public function renderView($view, $params = [])
    {
        Application::$app->view->renderView($view , $params);
    }

    public function renderContent($viewContent)
    {
        Application::$app->view->renderContent($viewContent);
    }

    public function layoutContent()
    {
        Application::$app->view->layoutContent();

    }

    public function renderOnlyView($view, $params = [])
    {
        Application::$app->view->renderOnlyView($view , $params);
    }
}
