# Allume Coding Challenge 
This application is meant to be a backend implimentation of a marketplace to connect Clients with Stylist to book appointments in 30 minute increments. I have chosen to expand on the initial requirements and exanded it to become an interactive API that a Web or Mobile application could both interact with using JSON.

## About
I have decided to use the minimal Slim Framework to bootstrap the application with some basic routing and database frameworks. Using composer it loads in the following packages:
* **slim/slim** the Slim framework for basic routing functionality.  
* **slim/twig-view** templating to help with the basic display of logs and appointments.  
* **respect/validation** a validation framework to simplify incoming data validation.  
* **monolog/monolog** a more extensive logging capability than Slim's to allow for JSON logging.  
* **illuminate/database** adds a database toolkit to speed up writing database queries.  

### Application in Depth
#### Containers
This application includes 3 containers:  
* **Application container** this container holds the source code of the application and runs an NGINX/PHP-FPM stack.  
    * This container has a wait script to ensure that MariaDB container is responsive before continuing.  
    * This container will run scripts contained within `/src/scripts` during bootup.  
        * The first script being called `composer-install` installs all the composer packages.  
        * The second script being called `setup-schema` sets up the database schema in the database setup in MariaDB container.  
* **Database container** this container holds the database for the application to hold orders.  It uses a docker volume to store database files to ensure it's state is maintained.  
* **Test container** this container runs CURL requests against the API in order to test for expected behavoir.  
* **PHPMyAdmin container** this container makes it easy to see what's happening on the database when the API is being utilized. You can visit this container using port `:8080` like [localhost:8080](http://localhost:8080).  

#### Data
The data is held within 2 tables `users` & `slots`.  

##### Users 
The users table holds both stylists and clients and they are differentiated by column `type`.  

##### Slots
The slots table holds both slots and appointments. An appointment is a slot that is booked by a client, meaning that `client_id` is not NULL and holds the **ID** of the client that has booked the slot.  

Slots are all kept in 30 minute increments with only the `slot_begin` being set.

#### Code
All code for the API are held within the `/src` directory. Below is a breakdown of the code by directory. I will breakdown the code that is mainly pertinent to this challenge. 

##### Breaking Time Into Datetime Increments
Every time that the `Slot` Model Class is instantiated it creates time increments in 30 minute increments. This happens in the method `setDatetimeIncrements`. 
The increments are broken up here and stuffed into a class variable `increments` in the code snippet below:
```php
while ($slot_length > 0) {
    $this->increments[] = self::convertToMysqlDate($Datetime);
    $datetime = self::addThirtyMinutes($Datetime);
    $slot_length -= 30;
}
```

##### Add Slot
After passing through the router it comes to `src/app/Controllers/Api/SlotController.php` method `postRecord`. The incoming data is validated before then being passed to `src/app/Models/Slot.php` method `addSlot`. Here is where the logic starts to happen.  

Since the increments are already created when the class is instantiated now we just need to loop through the increments and add them to the database. Using Eloquent `updateOrCreate` to create SQL that creates an UPSERT query that will overwrite or set `stylist_id`, `slot_begin` & `order_id`, leaving other fields alone meaning that if a slot is already booked it stays booked.  
```php 
foreach ($this->increments as $increment) {
    self::updateOrCreate(
        [
            'stylist_id' => $this->stylist_id, 
            'slot_begin' => $increment
        ],[
            'stylist_id' => $this->stylist_id, 
            'slot_begin' => $increment, 
            'order_id' => $this->params['order_id']
        ]
    );
}
```

##### Remove Slot
After passing through the router it comes to `src/app/Controllers/Api/SlotController.php` method `deleteRecord`. The incoming data is validated before then being passed to `src/app/Models/Slot.php` method `removeSlot`. Here is where the logic starts to happen.  

Here I added a bit of functionality, `all_or_nothing`, the capibility to return an `failure` if all the desired increments aren't available to be removed. If this is not chosen it will delete all slots in the given increments that are not booked.  

If deletion occurs it happens in the following snippet from `App\Models\Slot:deleteSlots`:
```php 
return self::whereIn('slot_begin', $this->increments)
    ->where('stylist_id', $this->stylist_id)
    ->whereNull('client_id')
    ->delete();
```
This code returns the number of records that were deleted.  

##### Book Appointment
After passing through the router it comes to `src/app/Controllers/Api/AppointmentController.php` method `postRecord`. The incoming data is validated before then being passed to `src/app/Models/Slot.php` method `addAppointment`. Here is where the logic starts to happen.  

To determine whether all available time increments are available the following code snippet is run from `App\Models\Slot:slotsOpenForDesiredStylist`:  
```php 
return self::whereIn('slot_begin', $this->increments)
    ->whereNull('client_id')
    ->where('stylist_id', $this->stylist_id)
    ->count() = count($this->increments);
```
Which returns a boolean of whether all available time increments are available for the desired stylist.  

Appointments are booked by simply updating those rows with the client id, which happens in the following snippet from `App\Models\Slot:updateSlotsForStylist`:  
```php 
return self::whereIn('slot_begin', $this->increments)
    ->where('stylist_id', $stylist_id)
    ->whereNull('client_id')
    ->havingRaw('COUNT(*) = ' . count($this->increments))
    ->update(['client_id' => $this->client_id]);
```
This method returns the number of rows update.  

###### Extra Credit
**flexible_in_stylist**  
The search for extra stylists that are available within the same time range happens within the following snippet from `App\Models\Slot:slotsOpenForAnyStylist`:  
```php 
return self::whereIn('slot_begin', $this->increments)
    ->whereNull('client_id')
    ->groupBy('stylist_id')
    ->havingRaw('COUNT(*) = ' . count($this->increments))
    ->get();
```  
This returns the rows of stylists that are available for the given time slots.  Then once we validate that we have retrieved the records we pull a random row and make it into an array so that we can retrieve the random stylists id `$slots_open_for_any_stylist->random()->toArray()`.  
Once we have that id we pass it to the method `App\Models\Slot:updateSlotsForStylist` covered under **Book Appointment**.  


## Options to Test
<a href="#test_locally">Test Locally</a>  
<a href="#test_remotely_with_postman">Remote Using Postman</a>  

## Test Locally

### Requirements
These are the requirements to run it on a local computer.  
* **Docker** How to [install](https://docs.docker.com/v17.12/install/)  
* **Docker-Compose** How to [install](https://docs.docker.com/compose/install/)  

## Test Remotely with Postman

### Requirements 
I have setup a testing container remotely with the same container and 