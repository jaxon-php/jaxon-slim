<?php

namespace Jaxon\Slim;

use Jaxon\Script\JsExpr;
use Jaxon\Script\JxnCall;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\PhpRenderer;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\TwigFilter;
use Twig\TwigFunction;

use function Jaxon\attr;
use function Jaxon\jaxon;
use function Jaxon\jq;
use function Jaxon\js;
use function Jaxon\pm;
use function Jaxon\rq;

class Helper
{
    /**
     * @param string|string[]      $path     Path(s) to templates directory
     * @param array<string, mixed> $settings Twig environment settings
     *
     * @throws LoaderError When the template cannot be found
     *
     * @return Twig
     */
    public static function twig(string|array $path, array $settings): Twig
    {
        $twig = Twig::create($path, $settings);
        $renderer = $twig->getEnvironment();

        // Filters for custom Jaxon attributes
        $renderer->addFilter(new TwigFilter('jxnHtml',
            fn(JxnCall $xJxnCall) => attr()->html($xJxnCall), ['is_safe' => ['html']]));
        $renderer->addFilter(new TwigFilter('jxnShow',
            fn(JxnCall $xJxnCall) => attr()->show($xJxnCall), ['is_safe' => ['html']]));
        $renderer->addFilter(new TwigFilter('jxnOn',
            fn(JsExpr $xJsExpr, string|array $on, array $options = []) =>
                attr()->on($on, $xJsExpr, $options), ['is_safe' => ['html']]));
        $renderer->addFilter(new TwigFilter('jxnClick',
            fn(JsExpr $xJsExpr, array $options = []) =>
                attr()->click($xJsExpr, $options), ['is_safe' => ['html']]));

        // Functions for custom Jaxon attributes
        $renderer->addFunction(new TwigFunction('jxnHtml',
            fn(JxnCall $xJxnCall) => attr()->html($xJxnCall), ['is_safe' => ['html']]));
        $renderer->addFunction(new TwigFunction('jxnShow',
            fn(JxnCall $xJxnCall) => attr()->show($xJxnCall), ['is_safe' => ['html']]));
        $renderer->addFunction(new TwigFunction('jxnOn',
            fn(string|array $on, JsExpr $xJsExpr, array $options = []) =>
                attr()->on($on, $xJsExpr, $options), ['is_safe' => ['html']]));
         $renderer->addFunction(new TwigFunction('jxnClick',
            fn(JsExpr $xJsExpr, array $options = []) =>
                attr()->click($xJsExpr, $options), ['is_safe' => ['html']]));
        $renderer->addFunction(new TwigFunction('jxnTarget',
            fn(string $name = '') => attr()->target($name), ['is_safe' => ['html']]));

        $renderer->addFunction(new TwigFunction('jq', fn(...$aParams) => jq(...$aParams)));
        $renderer->addFunction(new TwigFunction('js', fn(...$aParams) => js(...$aParams)));
        $renderer->addFunction(new TwigFunction('rq', fn(...$aParams) => rq(...$aParams)));
        $renderer->addFunction(new TwigFunction('pm', fn() => pm()));

        // Functions for Jaxon js and CSS codes
        $renderer->addFunction(new TwigFunction('jxnCss',
            fn() => jaxon()->css(), ['is_safe' => ['html']]));
        $renderer->addFunction(new TwigFunction('jxnJs',
            fn() => jaxon()->js(), ['is_safe' => ['html']]));
        $renderer->addFunction(new TwigFunction('jxnScript',
            fn() => jaxon()->script(), ['is_safe' => ['html']]));

        return $twig;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string                 $attributeName
     *
     * @return TwigView
     */
    public static function twigView(ServerRequestInterface $request, string $attributeName = 'view'): TwigView
    {
        return new TwigView(Twig::fromRequest($request, $attributeName));
    }

    /**
     * @param string $templatePath
     * @param array  $attributes
     * @param string $layout
     *
     * @return PhpView
     */
    public static function phpView(string $templatePath = '', array $attributes = [], string $layout = ''): PhpView
    {
        return new PhpView(new PhpRenderer($templatePath, $attributes, $layout));
    }
}
