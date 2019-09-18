#!/bin/bash

docker run -d -p 8080:80 --restart always -v $(pwd)/public/uploads:/var/www/html/public/uploads/ -v /var/www/repos/mailserver/config/opendkim/keys/storyoftheseason.com:/opendkim -e APP_ENV=prod --env-file .env.local --name storyoftheseason storyoftheseason