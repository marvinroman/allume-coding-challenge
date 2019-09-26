<?php 

namespace App\Interfaces;

use \Slim\Http\Request;
use \Slim\Http\Response;

interface DefaultApiControllerInterface 
{
    public function getRecord(Request $request, Response $response, Array $args) : Response;
    public function postRecord(Request $request, Response $response, Array $args) : Response;
    public function deleteRecord(Request $request, Response $response, Array $args) : Response;
    public function putRecord(Request $request, Response $response, Array $args) : Response;
}