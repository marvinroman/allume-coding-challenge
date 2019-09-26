<?php 

// GUI routes
$app->get('/', 'GuiController:index')->setName('home');
$app->get('/appointments', 'GuiController:viewAppointments')->setName('appointments');
$app->get('/logs', 'GuiController:viewLogs')->setName('logs');
$app->get('/slots', 'GuiController:viewSlots')->setName('slots');
$app->get('/users', 'GuiController:viewUsers')->setName('users');

// API routes
$app->group('/v1', function() {
    // appointment routes
    $this->get('/appointment{/id}', 'AppointmentController:getRecord');
    $this->post('/appointment', 'AppointmentController:postRecord');
    $this->delete('/appointment', 'AppointmentController:deleteRecord');
    $this->put('/appointment', 'AppointmentController:putRecord');
    // slot routes
    $this->get('/slot{/id}', 'SlotController:getRecord');
    $this->post('/slot', 'SlotController:postRecord');
    $this->delete('/slot', 'SlotController:deleteRecord');
    $this->put('/slot', 'SlotController:putRecord');
    //user routes
    $this->get('/user{/id}', 'UserController:getRecord');
    $this->post('/user', 'UserController:postRecord');
    $this->delete('/user', 'UserController:deleteRecord');
    $this->put('/user', 'UserController:putRecord');
});
