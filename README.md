Jaxon integration for the Slim Framework
========================================

This package is an extension to integrate the [Jaxon library](https://github.com/jaxon-php/jaxon-core) into the Slim framework.
It works with Slim version 4.

Installation
------------

Add the following lines in the `composer.json` file, and run the `composer update jaxon-php/` command.

```json
"require": {
    "jaxon-php/jaxon-slim": "^5.0"
}
```

Routing and middlewares
-----------------------

This package provides two Jaxon PSR middlewares, one to load the Jaxon config, and the other to process Jaxon requests.
The Jaxon config middleware must be attached to all the routes where the Jaxon features are enabled,
while the later must be attached to the route that processes Jaxon requests.

```php
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
    // Register the app container with the Jaxon library.
    if(($container = $app->getContainer()) !== null)
    {
        jaxon()->app()->setContainer($container);
    }

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

Configuration
-------------

The above example bootstraps the library from the `config/jaxon.php` file.
It must contain both the `app` and `lib` sections defined in the documentation (https://www.jaxon-php.org/docs/v5x/about/configuration.html).

An example is presented in the `config/config.php` file of this repo.

Setting the view renderer
-------------------------

The Slim framework provides two components for view rendering, and both can be used with the
[Jaxon view renderer](https://www.jaxon-php.org/docs/v3x/advanced/views.html).

The [Twig-View](https://github.com/slimphp/Twig-View) component displays Twig views.

```php
use Jaxon\Slim\Helper;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\TwigMiddleware;

// Add Twig-View Middleware
$twig = Helper::twig(__DIR__ . '/../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$jaxonConfigMiddleware = function(Request $request, RequestHandler $handler) {
    return jaxon()->psr()
        ->view('twig', '.html.twig', function() use($request) {
            return Helper::twigView($request);
        })
        ->config(__DIR__ . '/../jaxon/config.php')
        ->process($request, $handler);
};
```

The [PHP-View](https://github.com/slimphp/PHP-View) component displays PHP views.

```php
$jaxonConfigMiddleware = function(Request $request, RequestHandler $handler) {
    return jaxon()->psr()
        ->view('php', '.php', function() {
            return Helper::phpView(__DIR__ . '/../templates');
        })
        ->config(__DIR__ . '/../jaxon/config.php')
        ->process($request, $handler);
};
```

Twig functions
--------------

This extension provides the following Twig functions to insert Jaxon js and css codes in the pages that need to show Jaxon related content.

```php
// templates/demo/index.html.twig

<!-- In page header -->
{{ jxnCss() }}
</head>

<body>

<!-- Page content here -->

</body>

<!-- In page footer -->
{{ jxnJs() }}

{{ jxnScript() }}
```

Call factories
--------------

This extension registers the following Blade directives for Jaxon [call factories](https://www.jaxon-php.org/docs/v5x/ui-features/call-factories.html) functions.

> [!NOTE]
> In the following examples, the `rqAppTest` var in the template is set to the value `rq(Demo\Ajax\App\AppTest::class)`.

The `jxnBind` directive attaches a UI component to a DOM node, while the `jxnHtml` directive displays a component HTML code in a view.

```php
    <div class="col-md-12" {{ jxnBind(rqAppTest) }}>
        {{ jxnHtml(rqAppTest) }}
    </div>
```

The `jxnPagination` directive displays pagination links in a view.

```php
    <div class="col-md-12" {{ jxnPagination(rqAppTest) }}>
    </div>
```

The `jxnOn` directive binds an event on a DOM node to a Javascript call defined with a `call factory`.

```php
    <select class="form-control"
        {{ jxnOn('change', rqAppTest.setColor(jq()->val())) }}>
        <option value="black" selected="selected">Black</option>
        <option value="red">Red</option>
        <option value="green">Green</option>
        <option value="blue">Blue</option>
    </select>
```

The `jxnClick` directive is a shortcut to define a handler for the `click` event on a DOM node.

```php
    <button type="button" class="btn btn-primary"
        {{ jxnClick(rqAppTest.sayHello(true)) }}>Click me</button>
```

The `jxnEvent` directive defines a set of events handlers on the children of a DOM nodes, using `jQuery` selectors.

```php
    <div class="row" {{ jxnEvent([
        ['.app-color-choice', 'change', rqAppTest.setColor(jq()->val())]
        ['.ext-color-choice', 'change', rqExtTest.setColor(jq()->val())]
    ]) }}>
        <div class="col-md-12">
            <select class="form-control app-color-choice">
                <option value="black" selected="selected">Black</option>
                <option value="red">Red</option>
                <option value="green">Green</option>
                <option value="blue">Blue</option>
            </select>
        </div>
        <div class="col-md-12">
            <select class="form-control ext-color-choice">
                <option value="black" selected="selected">Black</option>
                <option value="red">Red</option>
                <option value="green">Green</option>
                <option value="blue">Blue</option>
            </select>
        </div>
    </div>
```

The `jxnEvent` directive takes as parameter an array in which each entry is an array with a `jQuery` selector, an event and a `call factory`.

Contribute
----------

- Issue Tracker: github.com/jaxon-php/jaxon-slim/issues
- Source Code: github.com/jaxon-php/jaxon-slim

License
-------

The package is licensed under the BSD license.
