<?php 

namespace App\Controllers\Api;

use \App\Models\User;
use \App\Controllers\ApiController;

/**
 * This class holds the methods for interaction with client users
 */
class UserController extends ApiController
{
    
    /**
     * Read user record(s)
     *
     * @param   Object  $request   Slim Request object
     * @param   Object  $response  Slim Response object
     * @param   Array   $args      Request params
     *
     * @return  Object             Slim Response Object
     */
    public function getRecord($request, $response, $args) 
    {
        #TODO return open users
        // Check if an ID is given for a single user else return all users
        // return JSON object of user(s)
    }

    /**
     * Create new user record(s)
     *
     * @param   Object  $request   Slim Request object
     * @param   Object  $response  Slim Response object
     * @param   Array   $args      Request params
     *
     * @return  Object             Slim Response Object
     */
    public function postRecord($request, $response, $args) 
    {
        #TODO create new user
        // validatate incoming information
        // check if there is an existing user with the same email
        // add user
        // return success
    }

    /**
     * Delete existing user record(s)
     *
     * @param   Object  $request   Slim Request object
     * @param   Object  $response  Slim Response object
     * @param   Array   $args      Request params
     *
     * @return  Object             Slim Response Object
     */
    public function deleteRecord($request, $response, $args) 
    {
        #TODO delete existing user
        // ensure that a user ID was given
        // delete user
        // return success
    }

    /**
     * Update existing user record(s)
     *
     * @param   Object  $request   Slim Request object
     * @param   Object  $response  Slim Response object
     * @param   Array   $args      Request params
     *
     * @return  Object             Slim Response Object
     */
    public function putRecord($request, $response, $args)
    {
        #TODO update existing user
        // check if ID was given
        // check if user exists
        // update user
        // return success
    }
}