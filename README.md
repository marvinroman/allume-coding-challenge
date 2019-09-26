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
This application includes 3 containers:
* **Application container** this container holds the source code of the application and runs an NGINX/PHP-FPM stack.  
* **Database container** this container holds the database for the application to hold orders.  
* 

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