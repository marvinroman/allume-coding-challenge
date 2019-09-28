<?php 

namespace App\Controllers\Api;

use \App\Models\Slot;
use \App\Models\User;
use \App\Controllers\ApiController;
use \Respect\Validation\Validator as v;
use \Slim\Http\Request;
use \Slim\Http\Response;

/**
 * The class holds the methods for interacting with stylist's slots
 */
class SlotController extends ApiController
{
    /**
     * Read slot record(s)
     *
     * @param   Request     $request    Slim Request object
     * @param   Response    $response   Slim Response object
     * @param   Array       $args       Request params
     *
     * @return  Response                Slim Response Object
     */
    public function getRecord(Request $request, Response $response, Array $args) : Response
    {
            return $response->withJson([
                'status'=> 'failed', 
                'message'=> 'Reading slots is not allowed.', 
            ], 400);
    }

    /**
     * Create new slot record(s)
     *
     * @param   Request     $request    Slim Request object
     * @param   Response    $response   Slim Response object
     * @param   Array       $args       Request params
     *
     * @return  Response                Slim Response Object
     */
    public function postRecord(Request $request, Response $response, Array $args) : Response
    {
        // create hash fingerprint for request
        $hash = hash('md5', implode(',', $request->getParams()));

        // log request along with hash
        $this->container->logger->info('Add Slot Request', ['hash' => $hash, 'request', $request->getParams()]);

        // Validate incoming user fields
        $validation = $this->container->validator->validate( $request, [ 
            'order_id' => v::intVal()->notEmpty(),
            'stylist_id' => v::intVal()->isStylist(),
            'slot_begin' => v::date()->notEmpty(),
            'slot_length_min' => v::intVal()->notEmpty()->multipleOfThirty(),
        ]);

        // if validation fails return error
        if ( $validation->failed() ) {
            $this->container->logger->error('Add Slot Failed Validation', [
                'hash' => $hash, 
                'errors' => $validation->errors()
                ]);
            return $response->withJson([
                'status'=> 'failed', 
                'message'=> 'Inoming data failed validation.', 
                'errors' => $validation->errors()
            ], 400);
        }

        $Slot = new Slot($request->getParams());
        $status = $Slot->addSlot();

        // status code will be 200 if sucessful
        if ($status['code'] == 200) {
            $this->container->logger->info('Add Slot Response', ['hash' => $hash, 'response' => $status]);
        } else {
            $this->container->logger->error('Add Slot Response', ['hash' => $hash, 'response' => $status]);
        }

        return $response->withJson($status, $status['code']);
    }

    /**
     * Delete existing slot record(s)
     *
     * @param   Request     $request    Slim Request object
     * @param   Response    $response   Slim Response object
     * @param   Array       $args       Request params
     *
     * @return  Response                Slim Response Object
     */
    public function deleteRecord(Request $request, Response $response, Array $args) : Response
    {
        #TODO delete existing slot
        // ensure that a slot ID was given
        // check if the slot is booked yet
        // if slot is not booked 
        // delete slot
        // return success
        // else 
        // return erorr
    }

    /**
     * Update existing slot record(s)
     *
     * @param   Request     $request    Slim Request object
     * @param   Response    $response   Slim Response object
     * @param   Array       $args       Request params
     *
     * @return  Response                Slim Response Object
     */
    public function putRecord(Request $request, Response $response, Array $args) : Response
    {
            return $response->withJson([
                'status'=> 'failed', 
                'message'=> 'Updated slots is not allowed.', 
            ], 400);
    }
}