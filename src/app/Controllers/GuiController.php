<?php 

namespace App\Controllers;

use \App\Models\User;
use \App\Models\Slot;
use \Slim\Views\Twig as View; 

class GuiController extends Controller
{

    public function index($request, $response, $args) 
    {
        $this->container->view->render($response, 'home.twig');
    }

    public function viewAppointments($request, $response, $args) 
    {
        $slots = Slot::whereNotNull('client_id')
        ->selectRaw('
        id,
        order_id,
        slot_begin,
        stylist_id,
        (SELECT name FROM users WHERE id = stylist_id) AS stylist_name,
        client_id,
        (SELECT name FROM users WHERE id = client_id) AS client_name
        ')
        ->get()
        ;
        $this->container->view->getEnvironment()->addGlobal('slots', $slots);
        $this->container->view->render($response, 'appointments.twig');
    }

    public function viewLogs($request, $response, $args) 
    {
        $logfile_path = __DIR__ . '/../../logs/api.log';
        $logs = [];
        if (file_exists($logfile_path)) {
            $logfile = fopen($logfile_path,"r");
        
            while(! feof($logfile)) {
                $logs[] = json_decode(fgets($logfile));
            }
    
            fclose($logfile);
            $this->container->view->getEnvironment()->addGlobal('logs', $logs);
            $this->container->view->render($response, 'logs.twig');
        } else {
            $this->container->flash->addMessage('dark', "There currently aren't any logs to analyze.");
            return $response->withRedirect($this->container->router->pathFor('home'));

        }
    }

    public function viewSlots($request, $response, $args) 
    {
        $slots = Slot::selectRaw('
        id,
        order_id,
        slot_begin,
        stylist_id,
        (SELECT name FROM users WHERE id = stylist_id) AS stylist_name,
        client_id,
        (SELECT name FROM users WHERE id = client_id) AS client_name
        ')
        ->get()
        ;
        $this->container->view->getEnvironment()->addGlobal('slots', $slots);
        $this->container->view->render($response, 'slots.twig');
    }

    public function viewUsers($request, $response, $args) 
    {
        $users = User::all();
        $this->container->view->getEnvironment()->addGlobal('users', $users);
        $this->container->view->render($response, 'users.twig');
    }
}