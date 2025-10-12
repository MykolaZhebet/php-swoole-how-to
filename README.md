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