#!/bin/bash

docker run -d -p 80:80 --restart always -e APP_ENV=prod --name web-server web-server