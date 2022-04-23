Jaxon Library for Slim
======================

This package integrates the [Jaxon library](https://github.com/jaxon-php/jaxon-core) into the Slim framework.

Installation
------------

The version 4 of the package requires Slim version 4.

Install the package with `Composer`.

```bash
composer require jaxon-php/jaxon-slim ^4.0
```
Or
```json
{
    "require": {
        "jaxon-php/jaxon-slim": "^4.0",
    }
}
```
And run `composer install`.

Routing and middlewares
-----------------------

This package uses two Jaxon PSR middlewares, one to load the Jaxon config, and the other to process Jaxon requests.
The Jaxon config middleware must be attached to all the routes where the Jaxon features are enabled,
while the later must be attached to the route that processes Jaxon requests.

```php
<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;
use function jaxon;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Jaxon middleware to load config
// Set a container if you need to use its services in Jaxon classes.
// Set a logger if you need to send messages to your logs in Jaxon classes.
$jaxonConfigMiddleware = function(Request $request, RequestHandler $handler) {
    return jaxon()->psr()
        // Uncomment the following line to set a container
        // ->container($container)
        // Uncomment the following line to set a logger
        // ->logger($logger)
        ->config(__DIR__ . '/../config/jaxon.php')->process($request, $handler);
};

// Process Jaxon ajax requests
$app->group('/', function() use($app) {
    // Jaxon middleware to process ajax requests
    $jaxonAjaxMiddleware = function(Request $request, RequestHandler $handler) {
        return jaxon()->psr()->ajax()->process($request, $handler);
    };

    $app->post('/jaxon', function($request, $response) {
        // Todo: return an error. Jaxon could not find a plugin to process the request.
    })->add($jaxonAjaxMiddleware);

    // Insert Jaxon codes in a page
    $app->get('/', function($request, $response) {
        // Display a page with Jaxon js and css codes.
        $jaxon = jaxon()−>app();
        $css = $jaxon->css();
        $js = $jaxon->js();
        $script = $jaxon->script();
        // Display the page
        ...
    });
})->add($jaxonConfigMiddleware);
```

Setting the view renderer
-------------------------

The Slim framework provides two components for view rendering, and both can be used with the
[Jaxon view renderer](https://www.jaxon-php.org/docs/v3x/advanced/views.html).

The [Twig-View](https://github.com/slimphp/Twig-View) component displays Twig views.

```php
// Add Twig-View Middleware
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$jaxonConfigMiddleware = function(Request $request, RequestHandler $handler) {
    return jaxon()->psr()
        ->view('slim', '.html.twig', function() use($request) {
            $view = Twig::fromRequest($request);
            return new \Jaxon\Slim\View($view);
        })
        ->config(__DIR__ . '/../jaxon/config.php')->process($request, $handler);
};
```

The [PHP-View](https://github.com/slimphp/PHP-View) component displays PHP views.

```php
$jaxonConfigMiddleware = function(Request $request, RequestHandler $handler) {
    return jaxon()->psr()
        ->view('slim', '.php', function() use($request) {
            $view = new PhpRenderer(__DIR__ . '/../templates');
            return new \Jaxon\Slim\View($view);
        })
        ->config(__DIR__ . '/../jaxon/config.php')->process($request, $handler);
};
```

Usage
-----

The settings in the `config/jaxon.php` config file are separated into two sections.
The options in the `lib` section are those of the Jaxon core library, while the options in the `app` sections are those of the Slim application.

The following options can be defined in the `app` section of the config file.

| Name | Description |
|------|---------------|
| directories | An array of directory containing Jaxon application classes |
| views   | An array of directory containing Jaxon application views |
| | | |

By default, the `views` array is empty. Views are rendered from the framework default location.
There's a single entry in the `directories` array with the following values.

| Name | Default value        | Description |
|------|----------------------|-------------|
| directory | ROOT . '/jaxon/ajax' | The directory of the Jaxon classes |
| namespace | \Jaxon\Ajax          | The namespace of the Jaxon classes |
| separator | .                    | The separator in Jaxon class names |
| protected | empty array          | Prevent Jaxon from exporting some methods |
| |                      | |

Usage
-----

### The Jaxon classes

The Jaxon classes can inherit from `\Jaxon\App\CallableClass`.
By default, they are located in the `jaxon/ajax` dir of the Slim application, and the associated namespace is `\Jaxon\Ajax`.

This is an example of a Jaxon class, defined in the `ROOT/jaxon/Ajax/HelloWorld.php` file.

```php
namespace Jaxon\Ajax;

class HelloWorld extends \Jaxon\App\CallableClass
{
    public function sayHello()
    {
        $this->response->assign('div2', 'innerHTML', 'Hello World!');
        return $this->response;
    }
}
```

Contribute
----------

- Issue Tracker: github.com/jaxon-php/jaxon-slim/issues
- Source Code: github.com/jaxon-php/jaxon-slim

License
-------

The package is licensed under the BSD license.
