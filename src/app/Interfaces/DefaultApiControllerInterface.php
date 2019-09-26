<?php 

namespace \App\Interfaces;

use Slim\Http\Request;
use Slim\Http\Response;

interface DefaultApiControllerInterface 
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
    public function getRecord(Request $request, Response $response, $args);

    /**
     * Creates new slot record(s)
     *
     * @param   Request     $request    Slim Request object
     * @param   Response    $response   Slim Response object
     * @param   Array       $args       Request params
     *
     * @return  Response                Slim Response Object
     */
    public function postRecord(Request $request, Response $response, $args);

    /**
     * Delete existing slot record(s)
     *
     * @param   Request     $request    Slim Request object
     * @param   Response    $response   Slim Response object
     * @param   Array       $args       Request params
     *
     * @return  Response                Slim Response Object
     */
    public function deleteRecord(Request $request, Response $response, $args);

    /**
     * Update existing slot record(s)
     *
     * @param   Request     $request    Slim Request object
     * @param   Response    $response   Slim Response object
     * @param   Array       $args       Request params
     *
     * @return  Response                Slim Response Object
     */
    public function putRecord(Request $request, Response $response, $args);
}