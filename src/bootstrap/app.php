<?php 

require_once __DIR__ . '/../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\JsonFormatter;
use Respect\Validation\Validator as v;

// instantiate \Slim\App with settings for application
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'logfile' => __DIR__ . '/../logs/api.log',
        'db' => [
            'driver' => 'mysql',
            'host' => $_SERVER['MYSQL_HOST'],
            'database' => $_SERVER['MYSQL_DATABASE'],
            'username' => $_SERVER['MYSQL_USER'],
            'password' => $_SERVER['MYSQL_PASSWORD'],
            'charset' => $_SERVER['utf8'],
            'collation' => $_SERVER['utf8_unicode_ci'],
            'prefix' => '',
        ],
    ],
    ]);

$container = $app->getContainer();

// Setup Monolog & add into Slim container
$container['logger'] = function ($container) {
    $logger = new \Monolog\Logger( 'api_log' );
    $stream_handler = new \Monolog\Handler\StreamHandler( $container['settings']['logfile'], Logger::INFO );
    $stream_handler->setFormatter( new \Monolog\Formatter\JsonFormatter() );
    $logger->pushHandler( $stream_handler );
    return $logger;
};

// Setup Eloquent database capsule to add into Slim container
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
// Add Eloquent to Slim container
$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};

// Setup Twig & add into Slim container
$container['view'] = function ($container) {
    
    // settup where Twig templates are held
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'cache' => false,
        ]);
        
    // Setup ability to use baseurl & pathfor methods in templates
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));
    
    // calculate css version by last changed for cache busting
    $base_css_path = pathinfo(__DIR__ . '/../css/app.css');
    $base_css_version = filemtime($_SERVER['DOCUMENT_ROOT'].$url);
    $view->getEnvironment()->addGlobal('base_css_version', $base_css_version);
    
    return $view;
    
};

// Add Validator into Slim container
$container['validator'] = function ($container) {
    return new App\Models\Validator;
};

// Add AppointmentController into Slim container
$container['AppointmentController'] = function ($container) {
    return new \App\Controllers\Api\AppointmentController($container);
};

// Add SlotController into Slim container
$container['UserController'] = function ($container) {
    return new \App\Controllers\Api\UserController($container);
};

// Add SlotController into Slim container
$container['SlotController'] = function ($container) {
    return new \App\Controllers\Api\SlotController($container);
};
// setup custom rules
v::with('App\\Models\\Validation\\Rules\\');

require_once __DIR__ . '/../app/routes.php';
