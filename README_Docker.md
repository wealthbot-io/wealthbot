
# 🐳 Docker Installation

## Description

This is a complete stack for running Symfony 5 into Docker containers using docker-compose tool.

It is composed by 3 containers:

- `nginx`, acting as the webserver.
- `php`, the PHP-FPM container with the 7.4 PHPversion.
- `db` which is the MySQL database container with a **MySQL 8.0** image.

## Installation

1. 😀 Clone this rep.

2. Run `docker-compose build && docker-compose up -d`

3.
`docker run --rm --interactive --tty \
 --volume $PWD:/app \
 composer install --ignore-platform-reqs --no-scripts`
 
4. The 3 containers are deployed: 

```
Creating symfony-docker_db_1    ... done
Creating symfony-docker_php_1   ... done
Creating symfony-docker_nginx_1 ... done
```
You can login to container with this command
1. get php container id  `docker ps`
2. the login into container `docker exec -it d79de1403460 /bin/bash`
