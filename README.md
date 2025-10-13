###PHP use-cases of swoole: https://www.swoole.com/

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

Components:
 - platesphp for templating html with dynamic data. See https://platesphp.com/
 - slim microframework for routing, middleware, etc.
 - ilexn/swoole-convert-psr7 to convert request/response to PSR7
 - nyholm/psr7 as PSR7 implementation for slim 