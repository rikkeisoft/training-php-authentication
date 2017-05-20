# Training PHP Authentication

Use [Slim Framework 3](https://www.slimframework.com/) with the PHP-View template renderer and Monolog logger.

This application was built for [Composer](https://getcomposer.org/)

## Install the Application

Run this command

```shell
$ git clone git@github.com:rikkeisoft/training-php-authentication.git /path/to/your-app
$ cd /path/to/your-app
$ composer install
```

Replace `/path/to/your-app` with the desired directory name for your new application. You'll want to:

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `logs/` is web writeable.

To run the application in development, you can also run this command. 

```shell
$ php -S 0.0.0.0:8080 -t public public/index.php
```

Open http://127.0.0.1:8080/ on your web browser

## Testing

Run this command to run the test suite

```shell
$ composer test
```

That's it! Now go build something cool.
