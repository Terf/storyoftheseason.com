#!/bin/bash

docker run --name myadmin --restart always -d --link db:db -p 8082:80 phpmyadmin/phpmyadmin