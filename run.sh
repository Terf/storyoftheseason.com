#!/bin/bash

docker run -d -p 8080:80 --restart always -v $(pwd)/public/uploads:/var/www/html/public/uploads/ -v /var/run/docker.sock:/var/run/docker.sock -e APP_ENV=prod --env-file .env.local --name storyoftheseason storyoftheseason