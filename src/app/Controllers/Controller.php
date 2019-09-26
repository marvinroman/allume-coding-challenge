<?php 

namespace App\Controllers;

/**
 * Base controller used to load in methods across all controllers
 */
abstract class Controller
{

    protected $container;

    /**
     * make Slim container available to all controllers
     *
     * @param   [type]  $container  [$container description]
     *
     * @return  [type]              [return description]
     */
    public function __construct($container) 
    {
        $this->container = $container;
    }
}