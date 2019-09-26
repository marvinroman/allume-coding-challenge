<?php 

namespace App\Controllers;

use \Respect\Validation\Validator as v;

/**
 * Base Api controller used to apply API interface across all API controllers
 */
class ApiController implements DefaultApiControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRecord($request, $response, $args) 
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postRecord($request, $response, $args) 
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord($request, $response, $args) 
    {
    }

    /**
     * {@inheritdoc}
     */
    public function putRecord($request, $response, $args)
    {
    }

}