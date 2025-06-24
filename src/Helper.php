<?php

namespace Jaxon\Slim;

use Jaxon\Script\Call\JxnCall;
use Jaxon\Script\JsExpr;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\PhpRenderer;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\TwigFilter;
use Twig\TwigFunction;

use function Jaxon\attr;
use function Jaxon\jaxon;
use function Jaxon\je;
use function Jaxon\jo;
use function Jaxon\jq;
use function Jaxon\rq;

class Helper
{
    /**
     * @param array $events
     *
     * @return string
     */
    private static function setJxnEvent(array $events): string
    {
        return isset($events[0]) && is_array($events[0]) ?
            attr()->events($events) : attr()->event($events);
    }

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
        $renderer->addFilter(new TwigFilter('jxnBind',
            fn(JxnCall $xJxnCall, string $item = '') =>
                attr()->bind($xJxnCall, $item), ['is_safe' => ['html']]));
        $renderer->addFilter(new TwigFilter('jxnPagination',
            fn(JxnCall $xJxnCall) => attr()->pagination($xJxnCall), ['is_safe' => ['html']]));
        $renderer->addFilter(new TwigFilter('jxnOn',
            fn(JsExpr $xJsExpr, string $event) =>
                attr()->on($event, $xJsExpr), ['is_safe' => ['html']]));
        $renderer->addFilter(new TwigFilter('jxnClick',
            fn(JsExpr $xJsExpr) => attr()->click($xJsExpr), ['is_safe' => ['html']]));
        $renderer->addFilter(new TwigFilter('jxnEvent',
            fn(array $events) => self::setJxnEvent($events), ['is_safe' => ['html']]));

        // Functions for custom Jaxon attributes
        $renderer->addFunction(new TwigFunction('jxnHtml',
            fn(JxnCall $xJxnCall) => attr()->html($xJxnCall), ['is_safe' => ['html']]));
        $renderer->addFunction(new TwigFunction('jxnBind',
            fn(JxnCall $xJxnCall, string $item = '') =>
                attr()->bind($xJxnCall, $item), ['is_safe' => ['html']]));
        $renderer->addFunction(new TwigFunction('jxnPagination',
            fn(JxnCall $xJxnCall) => attr()->pagination($xJxnCall), ['is_safe' => ['html']]));
        $renderer->addFunction(new TwigFunction('jxnOn',
            fn(string $event, JsExpr $xJsExpr) =>
                attr()->on($event, $xJsExpr), ['is_safe' => ['html']]));
         $renderer->addFunction(new TwigFunction('jxnClick',
            fn(JsExpr $xJsExpr) => attr()->click($xJsExpr), ['is_safe' => ['html']]));
        $renderer->addFunction(new TwigFunction('jxnEvent',
            fn(array $events) => self::setJxnEvent($events), ['is_safe' => ['html']]));

        $renderer->addFunction(new TwigFunction('jq', fn(...$aParams) => jq(...$aParams)));
        $renderer->addFunction(new TwigFunction('je', fn(...$aParams) => je(...$aParams)));
        $renderer->addFunction(new TwigFunction('jo', fn(...$aParams) => jo(...$aParams)));
        $renderer->addFunction(new TwigFunction('rq', fn(...$aParams) => rq(...$aParams)));

        // Functions for Jaxon js and CSS codes
        $renderer->addFunction(new TwigFunction('jxnCss',
            fn() => jaxon()->css(), ['is_safe' => ['html']]));
        $renderer->addFunction(new TwigFunction('jxnJs',
            fn() => jaxon()->js(), ['is_safe' => ['html']]));
        $renderer->addFunction(new TwigFunction('jxnScript',
            fn(bool $bIncludeJs = false, bool $bIncludeCss = false) =>
                jaxon()->script($bIncludeJs, $bIncludeCss), ['is_safe' => ['html']]));

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
