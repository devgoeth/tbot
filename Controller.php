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
    public $menuArray = [
        '' => []
    ];

    public function __construct($base)
    {
        $this->base = $base->base;
        $this->params = $base->params;
        $this->config = $base->config;
        $this->menuArray = $base->menuArray;
    }
}