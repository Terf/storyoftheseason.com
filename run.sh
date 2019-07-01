#!/bin/bash

docker run -d -p 8080:80 --restart always -e APP_ENV=prod --name web-server web-server