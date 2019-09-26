<?php 

namespace App\Controllers;

use \App\Interfaces\DefaultApiControllerInterface;

/**
 * Base Api controller used to apply API interface across all API controllers
 */
abstract class ApiController extends Controller implements DefaultApiControllerInterface
{

}