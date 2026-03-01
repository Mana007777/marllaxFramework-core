<?php

namespace app\Core;

use app\Core\Middlwares\BaseMiddleware;

class Controller
{

    public string $layout = 'main';
    public string $action = '';
    /**
     * @var BaseMiddleware
    */
    public array $middlewares = [];
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }
    protected function render($view, $params = [])
    {
        return Application::$app->router->renderView($view, $params);
    }

    public function registerMiddlware(BaseMiddleware $middlware)
    {
        $this->middlewares[] = $middlware;
    }
    public function getMiddleware(){
        return $this->middlewares;
    }
}