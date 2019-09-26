<?php 

namespace App\Controllers\Api;

use \App\Models\Slot;
use \App\Controllers\ApiController;

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
    public function getRecord($request, $response, $args) 
    {
        #TODO return open slots
        // Check if an ID is given for a single slot else return all slots
        // return JSON object of slot(s)
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
    public function postRecord($request, $response, $args) 
    {
        #TODO create new slot
        // validatate incoming information
        // check to make sure there isn't a slot for the given time already for given stylist
        // add slot
        // return success
        // else 
        // return error
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
    public function deleteRecord($request, $response, $args) 
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
    public function putRecord($request, $response, $args)
    {
        #TODO update existing slot
        // return error slots are allowed to be updated
    }
}