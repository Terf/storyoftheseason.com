#!/bin/bash

. db.config
docker volume create mysql
docker run --name db --restart always -e MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD -p 3306:3306 -v mysql:/var/lib/mysql -v $(pwd)/mysql:/etc/mysql/conf.d -d mysql