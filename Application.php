<?php

namespace app\Core;

use app\Core\db\Database;

class Application
{
    public string $layout = "main";
    public static string $ROOT_DIR;
    public string $userClass;
    public Router $router;
    public Request $request;

    public Response $response;

    public static Application $app;

    public ?Controller $controller = null;
    public Database $db;
    public Session $session;
    public ?UserModel $user;
    public View $view;
    public function __construct($rootPath, array $config = [])
    {
        $this->userClass = $config['userClass'];
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->db = new Database($config['db'] ?? []);
        $this->session = new Session();
        $this->view = new View();
        $this->userClass = $config['userClass'];
        $primaryValue = $this->session->get('user');
        if ($primaryValue) {
            $primaryKey = $this->userClass::primaryKey();
            $this->user = $this->userClass::findOne([$primaryKey => $primaryValue]);
        } else {
            $this->user = null;
        }
    }


    public function run()
    {
        try {
            $this->router->resolve();
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode());

            echo $this->router->renderView('_error', ['exception' => $e]);
        }
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function login(UserModel $user)
    {
        $this->user = $user;
        $primaryKey = $user->primaryKey();
        $primaryKey = $user->{$primaryKey};
        $this->session->set('user', $primaryKey);
        return true;
    }
    public function logout()
    {
        $this->user = null;
        $this->session->remove('user');
    }
    public static function isGuest()
    {
        return !self::$app->user;
    }
}
