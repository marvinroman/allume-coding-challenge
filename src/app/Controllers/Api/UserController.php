<?php 

namespace App\Controllers\Api;

use \App\Models\User;
use \App\Controllers\ApiController;
use \App\Interfaces\DefaultApiControllerInterface;
use \Respect\Validation\Validator as v;
use \Slim\Http\Request;
use \Slim\Http\Response;
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
    public function getRecord(Request $request, Response $response, Array $args) : Response
    {
        #TODO return open users
        // Check if an ID is given for a single user else return all users
        // return JSON object of user(s)
    }

    /**
     * Create new user record(s)
     *
     * @param   Request  $request   Slim Request object
     * @param   Response  $response  Slim Response object
     * @param   Array   $args      Request params
     *
     * @return  Response             Slim Response Object
     */
    public function postRecord(Request $request, Response $response, Array $args) : Response
    {
        // create hash fingerprint for request
        $hash = hash('md5', implode(',', $request->getParams()));

        // log request along with hash
        $this->container->logger->info('Add User Request', ['hash' => $hash, 'request', $request->getParams()]);

        // Validate incoming user fields
        $validation = $this->container->validator->validate( $request, [ 
            'name' => v::stringType()->length(1, 50)->notEmpty(),
            'email' => v::email()->notEmpty(),
            'type' => v::intVal()->notEmpty(),
        ]);

        // if validation fails return error
        if ( $validation->failed() ) {
            $this->container->logger->error('Add User Failed Validation', ['hash' => $hash, 'request', $request->getParams()]);
            return $response->withJson(['status'=> 'failed', 'message'=> 'Inoming data failed validation.'], 400);
        }

        $User = new User();
        // attempt to add user to table users
        $status = $User->addUser($request->getParams());

        // status code will be 200 if sucessful
        if ($status['code'] == 200) {
            $this->container->logger->info('Add User Response', ['hash' => $hash, 'response' => $status]);
        } else {
            $this->container->logger->error('Add User Response', ['hash' => $hash, 'response' => $status]);
        }
        return $response->withJson($status, $status['code']);
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
    public function deleteRecord(Request $request, Response $response, Array $args) : Response 
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
    public function putRecord(Request $request, Response $response, Array $args) : Response
    {
        #TODO update existing user
        // check if ID was given
        // check if user exists
        // update user
        // return success
    }
}