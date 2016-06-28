
##Devless Api Engine(DAE)

**Devless Api Engine(DAE)** is an opensource api engine that allows a crud access to databases as well as execute php scripts. 

Current implementation of the devless api engine is in php and ontop of the laravel framework. 

**DAE** can be used as a standalone (accessed solely via api calls ) however a management console is provided to interact with the api engine.

This document explains the various syntax for accessing and working with  the api engine.

**Requiments**
* Database (mysql, postgres, sqlsrv etc..)
* An HTTP server
* PHP >= 5.5.9
* OpenSSL PHP Extension
* PDO PHP Extension
* Mbstring PHP Extension
* Tokenizer PHP Extension
* composer

**Installation procedure**
* Clone the repo (git clone https://github.com/DevlessTeam/DV-PHP-CORE.git) 
* cd ../DV-PHP-CORE
* run composer install to grab dependecies
* copy .env.example to .env and update the database options 
* run migrations with php artisan migrate
* ``$ php artisan serve``

If everything goes on smoothly you should be able to access the setup screen at localhost:8000

If you will need help setting up you may check out the laravel [installation](https://laravel.com/docs/5.1) guide as the devless core is based of laravel. 

You may also seek help from the [devless forum](forum.devless.io) and also get started with the [devless application](docs.devless.io)
## How to contribute 
**Devless api Engine is currently not open for contribution**
* But we would love to hear from  you though. email us @ edmond@devless.io
* Please dont forget to visit our landing page @ [devless.io](devless.io)
