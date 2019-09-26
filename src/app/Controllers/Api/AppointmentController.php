<?php 

namespace App\Controllers\Api;

use \App\Models\Slim;
use \App\Models\Appointment;
use \App\Controllers\ApiController;

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
    public function getRecord($request, $response, $args) 
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
    public function postRecord($request, $response, $args) 
    {
        #TODO create new appointment
        // validatate incoming information
        // check if there is an open appointment slot for the specified stylist
        // add appointment
        // return success
        // else 
        // return error
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
    public function deleteRecord($request, $response, $args) 
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
    public function putRecord($request, $response, $args)
    {
        #TODO update existing appointment
        // check if ID was given
        // check if appointment exists
        // update appointment
        // return success
    }
}