# MajestiCloud API
This is the code that will run the new version of MajestiCloud (formerly SmartNet AppSync).

## Principle
This API is meant to run on any web server capable of processing HTTP(S) (GET, POST, PUT, PATCH and DELETE) requests, and running PHP code.
It is framework-less, meaning it has the least dependencies possible.

## Dependencies
- PHP 8.0
- Apache (or any other web server you want to use)
- MySQL (or any DBMS you want to use)
- (Optional) An SMTP server

## Setup steps
1. Install PHP 8.0, a web server and a DBMS.
2. Upload the files on your web server.
3. Configure your reverse proxy or VirtualHost for it to serve the API from /public_endpoints.
4. Ensure your web server has the right to write in /user_content and /engine/failure_logs.
5. Configure your environment variables by editing /engine/Environment.class.php, or add them in /.env
6. Create the database (Use majesticloud.sql).

## Using a different DBMS
If you choose to use a DBMS that is not MySQL, you **must** edit the PDO connection string in /engine/GlobalPDO.class.php.

## Security
- *Please* encrypt the traffic on your web server with HTTPS.
- *Please* change the default API key (otherwise everyone will be able to create users and get authorization codes)

## Developer
Created in 2023 by Quentin Pugeat <contact@quentinpugeat.fr>