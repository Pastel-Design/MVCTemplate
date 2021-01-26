<?php

namespace app\controllers;

use app\models\DbManager;

/**
 * Controller DefaultController
 *
 * @package app\controllers
 */
class DefaultController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sets default homepage
     *
     * @param array      $params
     * @param array|null $gets
     *
     * @return void
     */
    public function process(array $params, array $gets = null)
    {
        $this->head['page_title'] = "Template title";
        $this->head['page_keywords'] = "template,title";
        $this->head['page_description'] = "Template project description";
        $this->setView('default');
    }
}
