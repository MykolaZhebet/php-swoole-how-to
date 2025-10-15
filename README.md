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
docker compose run --rm app php ./src/server.php
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

TODOS:
 - db managements(dbal query builder/migrations)
 - console commands(symfony console?)
 - tests(symfony testCases?)