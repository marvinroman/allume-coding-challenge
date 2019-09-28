<?php 

namespace App\Controllers\Api;


use \App\Models\Slot;
use \App\Models\User;
use \App\Controllers\ApiController;
use \Respect\Validation\Validator as v;
use \Slim\Http\Request;
use \Slim\Http\Response;

/**
 * This class holds the methods for interaction with client appointments
 */
class AppointmentController extends ApiController
{
    
    /**
     * Read appointment record(s)
     *
     * @param   Object  $request   Slim Request object
     * @param   Object  $response  Slim Response object
     * @param   Array   $args      Request params
     *
     * @return  Object             Slim Response Object
     */
    public function getRecord(Request $request, Response $response, Array $args) : Response 
    {
        #TODO return open appointments
        // Check if an ID is given for a single appointment else return all appointments
        // return JSON object of appointment(s)
    }

    /**
     * Create new appointment record(s)
     *
     * @param   Object  $request   Slim Request object
     * @param   Object  $response  Slim Response object
     * @param   Array   $args      Request params
     *
     * @return  Object             Slim Response Object
     */
    public function postRecord(Request $request, Response $response, Array $args) : Response 
    {
        // create hash fingerprint for request
        $hash = hash('md5', implode(',', $request->getParams()));

        // log request along with hash
        $this->container->logger->info('Add Appointment Request', ['hash' => $hash, 'request', $request->getParams()]);

        // Validate incoming user fields
        $validation = $this->container->validator->validate( $request, [ 
            'order_id' => v::intVal()->notEmpty(),
            'stylist_id' => v::intVal()->notEmpty()->isStylist(),
            'client_id' => v::intVal()->notEmpty()->isClient(),
            'slot_begin' => v::date()->notEmpty(),
            'slot_length_min' => v::intVal()->notEmpty()->multipleOfThirty(),
            'flexible_in_time' => v::optional(v::boolVal()),
            'flexible_in_stylist' => v::optional(v::boolVal()),
        ]);

        // if validation fails return error
        if ( $validation->failed() ) {
            $this->container->logger->error('Add Appointment Failed Validation', [
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
        $status = $Slot->addAppointment();

        // status code will be 200 if sucessful
        if ($status['code'] == 200) {
            $this->container->logger->info('Add Appointment Response', ['hash' => $hash, 'response' => $status]);
        } else {
            $this->container->logger->error('Add Appointment Response', ['hash' => $hash, 'response' => $status]);
        }

        return $response->withJson($status, $status['code']);
    }

    /**
     * Delete existing appointment record(s)
     *
     * @param   Object  $request   Slim Request object
     * @param   Object  $response  Slim Response object
     * @param   Array   $args      Request params
     *
     * @return  Object             Slim Response Object
     */
    public function deleteRecord(Request $request, Response $response, Array $args) : Response 
    {
        #TODO delete existing appointment
        // ensure that a appointment ID was given
        // delete appointment
        // return success
    }

    /**
     * Update existing appointment record(s)
     *
     * @param   Object  $request   Slim Request object
     * @param   Object  $response  Slim Response object
     * @param   Array   $args      Request params
     *
     * @return  Object             Slim Response Object
     */
    public function putRecord(Request $request, Response $response, Array $args) : Response
    {
        #TODO update existing appointment
        // check if ID was given
        // check if appointment exists
        // update appointment
        // return success
    }
}