<?php

namespace app\router;

require(__DIR__ . "/../../vendor/autoload.php");

use app\config\DbConfig;
use App\controllers\Controller;
use app\models\DbManager;
use PDOException;

/**
 * Router
 *
 * @package app\router
 */
final class Router
{
    /**
     * @var Controller $data
     */
    protected Controller $controller;
    protected array $parameters;
    protected array $query;

    public function process($params)
    {
        /* URL Parsing */
        $this->parseURL($params[0]);
        /* Database connection tryout */
        $this->connectDatabase();
        /* Controller name init */
        $controller = $this->dashToCamel(array_shift($this->parameters));
        /* Controller class init */
        if (file_exists('../app/controllers/' . $controller . 'Controller.php')) {
            $controllerClass = "\app\controllers\\" . $controller . "Controller";
            $this->controller = new $controllerClass;
        } else {
            $this->reroute('error/404');
        }
        /* Controller preparing*/
        $this->controller->controllerName = $controller;
        if ($this->controller->isActive()) {
            $this->controller->process($this->parameters, $this->query);
            $this->controller->writeView();
        } else {
            $this->reroute("default");
        }
    }

    /**
     * @param string $url
     *
     * @return void
     */
    private function parseURL(string $url): void
    {
        $url = parse_url($url);
        $parsedURL = ltrim($url["path"], "/");
        $parsedURL = trim($parsedURL);
        $parsedURL = explode("/", $parsedURL);
        $parameters = array();
        foreach ($parsedURL as $parse) {
            if ($parse !== '') {
                $parameters[] = $parse;
            } else {
                break;
            }
        }
        if (empty($parameters[0])) {
            array_unshift($parameters, "default");
        }
        $this->setParameters($parameters);
        $query = array();
        if (isset($url["query"])) {
            parse_str($url["query"], $query);
        }
        $this->setQuery($query);
    }

    /**
     * @param string $text
     *
     * @return string|string[]
     */
    private function dashToCamel(string $text)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $text)));
    }

    /**
     * @param string $url
     *
     * @return void
     */
    static function reroute(string $url): void
    {
        header("Location: /$url");
        header("Connection: close");
        exit;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @param array $query
     */
    public function setQuery(array $query): void
    {
        $this->query = $query;
    }

    /**
     * @return void
     */
    private function connectDatabase(): void
    {
        try {
            DbManager::connect(DbConfig::$host, DbConfig::$username, DbConfig::$pass, DbConfig::$database);
        } catch (PDOException $exception) {
            if ($this->parameters[0] != "error") {
                $this->reroute("error/500");
            }
        }
    }
}
