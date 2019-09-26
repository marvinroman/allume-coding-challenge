<?php 

namespace App\Controllers;

use \Slim\Views\Twig as View; 

class GuiController extends Controller
{

    public function index($request, $response, $args) 
    {
        #TODO render blank page with menu
    }

    public function viewAppointments($request, $response, $args) 
    {
        #TODO render page that lists appointments
    }

    public function viewLogs($request, $response, $args) 
    {
        #TODO render page that lists logs
    }

    public function viewSlots($request, $response, $args) 
    {
        #TODO render page that lists slots
    }

    public function viewUsers($request, $response, $args) 
    {
        #TODO render page that lists users
    }
}