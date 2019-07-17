#!/bin/bash

. .env.local
docker run -d -p 8080:80 --restart always -v $(pwd)/public/uploads:/var/www/html/public/uploads/ -e APP_ENV=prod -e ADMIN_PASSWORD=$ADMIN_PASSWORD --name storyoftheseason storyoftheseason