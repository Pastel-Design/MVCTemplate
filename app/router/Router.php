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

    public function process($params)
    {

        $parsedParams = $this->parseURL($params[0]);
        /**
         * @var array $parsedGET
         * @var array $parsedURL
         */
        extract($parsedParams);

        if (empty($parsedURL[0])) {
            array_unshift($parsedURL, "default");
        }
        try {
            DbManager::connect(DbConfig::$host, DbConfig::$username, DbConfig::$pass, DbConfig::$database);
        } catch (PDOException $exception) {
            if ($parsedURL[0] != "error") {
                $this->reroute("error/500");
            }
        }
        $controllerName = $this->dashToCamel(array_shift($parsedURL));
        $controllerClass = $controllerName . 'Controller';

        if (file_exists('../app/controllers/' . $controllerClass . '.php')) {
            $controllerClass = "\app\controllers\\" . $controllerClass;
            $this->controller = new $controllerClass;
        } else {
            $this->reroute('error/404');
        }
        $this->controller->controllerName = $controllerName;
        $this->controller->process($parsedURL, $parsedGET);
        $this->controller->writeView();
    }

    /**
     * @param string $url
     *
     * @return false|string[]
     */
    private function parseURL(string $url)
    {
        $url = parse_url($url);
        $parsedURL = ltrim($url["path"], "/");
        $parsedURL = trim($parsedURL);
        $parsedURL = explode("/", $parsedURL);
        $return["parsedURL"] = array();
        foreach ($parsedURL as $parse) {
            if ($parse !== '') {
                $return["parsedURL"][] = $parse;
            } else {
                break;
            }
        }
        $return["parsedGET"] = array();
        if (isset($url["query"])) {
            parse_str($url["query"],$return["parsedGET"]);
        }
        return $return;
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
}
