# MajestiCloud API
This is the code that will run the new version of MajestiCloud (formerly SmartNet AppSync).

## Principle
This API is meant to run on any web server capable of processing HTTP(S) (GET, POST, PUT, PATCH and DELETE) requests, and running PHP code.
It is framework-less, meaning it has the least dependencies possible.

## Dependencies
PHP 8.0
Apache or Nginx
An SMTP server

## Setup steps
1. Install PHP 8.0 and a web server.
2. Upload the files on your web server.
3. Configure your reverse proxy or VirtualHost for it to serve the API from /public_endpoints.
4. Ensure your web server has the right to write in /user_content and /engine/failure_logs.
5. Configure your environment variables by editing /engine/Environment.class.php, or add them in /.env

## Developer
Created in 2023 by Quentin Pugeat <contact@quentinpugeat.fr>