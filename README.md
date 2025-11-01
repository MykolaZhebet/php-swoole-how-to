### PHP use-cases of swoole: https://www.swoole.com/
### Inspired by https://www.youtube.com/watch?v=VDLykjjrezM&list=PLYWCHRaNLGT-55hyJ0y9g7O8B0N8QrBmr&index=4

## Dockerization: 
- https://github.com/swoole/docker-swoole
- https://hub.docker.com/r/phpswoole/swoole

Run commands:
```
docker compose up
#test env. variables
docker-compose exec app env
docker compose run --rm app php ./server.php
#Migration
docker-compose exec app php ./server.php migration:migrate
#test
docker-compose exec app  ./vendor/bin/phpunit 
#concrete test
docker-compose exec app  ./vendor/bin/phpunit ./tests/Unit/GenerateTokenCommandTest.php
```
Build frontend:
``` 
npm install
npx mix build

```
DB:
```
docker compose exec -ti db bash
mysql -u root -h localhost -p
```
Run phpmyadmin: ``
Local env links:
 - [PHPMyAdmin](http://localhost:8080/)

Components:
 - platesphp for templating html with dynamic data. See https://platesphp.com/
 - slim microframework for routing, middleware, etc.
 - ilexn/swoole-convert-psr7 to convert request/response to PSR7
 - nyholm/psr7 as PSR7 implementation for slim
 - blucas/phpdotenv for working with .env file and have environment variables autoloading
 - ramsey/uuid for work with uuids
 - psr/simle-cache interface(PSR-16) for caching
 - nesbot/carbon - for work with dates
 - monolog/monolog for logging
 - php-di/php-di for DI container
 - illuminate/database Eloquent ORM(Laravel) for database
 - symfony/console for console commands
 - firebase/php-jwt for work with JWT tokens
 - syfmony/validator for validation input data
 - league/lysystem for work with filesystem
 - mustache/mustache for templating files
 - fakerphp/faker for generating dummy data
 - symfony/password-hasher for password hashing
 - kanata-php/socket-conveyor for handling websockets
### Frontend:
- laravel-mix for compiling assets in laravel
- socket-conveyor-client for client communication through websockets


##Troubleshoot WS:
```
sudo lsof -i :8004
sudo netstat -tlnp | grep 8004
wscat -c ws://localhost:8004
```
TODOS:
 - db managements(dbal query builder/migrations)
 - console commands(symfony console?)
 - tests(symfony testCases?)