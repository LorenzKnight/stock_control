# Clean Docker with PHP
Docker with PHP 7.4 fpm, Nginx, Composer, PhpUnit and postgresDB

## Starting app
make start

## other command to start the app
docker-compose up -d
or
docker-compose up --build --force-recreate -d

## short down the app
make down

## other command to short down the app
docker-compose down 

## Migrate or create the tables in the data-base
make migrate

## create the local environment
*For macOS:
1. You can install with Homebrew (if you have it):

brew install node
npm install -g ngrok
ngrok http 8889

2. Create your free account
Go to 👉 https://dashboard.ngrok.com/signup

Create an account (you can use your email or your GitHub/Google account)

3. Get your Authtoken
Once you're in the ngrok dashboard, go to:
👉 https://dashboard.ngrok.com/get-started/your-authtoken

You'll see a line similar to:

ngrok config add-authtoken 2Ktxb2XXXXXXXXXXXXXXX

4. Run this command in your terminal.
Copy and paste exactly as you see it in ngrok, for example:

ngrok config add-authtoken 2Ktxb2XXXXXXXXXXXXXXX
This links your local installation to your free account.

5. Rerun ngrok

ngrok http 8889

And you will see something like this:

Forwarding https://abc123.ngrok-free.app -> http://localhost:8889

6. Copy the public URL:
👉 https://abc123.ngrok-free.app

Go to Stripe → Webhooks → Add endpoint
Use:

https://abc123.ngrok-free.app/api/stripe_webhook.php
Done! You can now test webhooks locally as if you were in production. 🎯

7. update stripe_webhook.php

this line:
<!-- $endpointSecret = 'whsec_YrXZi2jJDbN5hmQ12pNrH53jXhPWKOhf'; -->

## Main page - localhost
http://localhost:8889/

## Php info
http://localhost:8889/php_info.php

## Xdebug info
http://localhost:8889/xdebug_info.php

## Running tests
docker-compose run php vendor/bin/phpunit

## Namespaces
change namespace "Example" in composer.json line 7 for your project name

## Connecting to MySql
1. User: admin
2. Passwd: Admin456
3. Port 5433
4. DB: stock_control_db

## Using PhpUnit in PhpStorm
1. PhpUnit By Remote Interpreter
2. Provide full docker path to autoloader.php /opt/project/vendor/autoload.php

## Problems and solusions
1. database issues: "Access denied for user 'root'@'localhost' (using password: YES)"
   Solusion: 
            Warning: this will permanently delete the contents in your db_data volume, wiping out any previous database you had there.

            docker-compose down -v
            docker-compose up -d

## How to know the ip of you db in docker
1. docker inspect name_of_your_db | grep IPAddress

## Enable nginx logs
in default.conf uncomment the lines:
access_log /var/log/nginx/default.log;
error_log /var/log/nginx/default.log;

### by Lorenz Knight