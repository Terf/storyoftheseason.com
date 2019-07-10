#!/bin/bash

docker run -d -p 8080:80 --restart always -v $(pwd)/public/uploads:/var/www/html/public/uploads/ -e APP_ENV=prod --name web-server web-server