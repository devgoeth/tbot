<?php

namespace devgoeth\tbot;


/**
 * Class Controller
 * @package devgoeth\tbot\Controller
 */
class Controller
{
    public $base;
    public $params;
    public $config;
    /**
     * @var devgoeth\tbot\models\State
     */
    public $state;
    public $menuArray = [
        '' => []
    ];

    public function __construct($base)
    {
        $this->base = $base;
        $this->state = $base->state;
        $this->params = $base->params;
        $this->config = $base->config;
        $this->menuArray = $base->menuArray;
    }
}