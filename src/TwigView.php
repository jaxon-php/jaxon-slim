<?php

namespace Jaxon\Slim;

use Jaxon\App\View\Store;
use Jaxon\App\View\ViewInterface;
use Slim\Views\Twig;

use function ltrim;
use function str_replace;
use function trim;

class TwigView implements ViewInterface
{
    /**
     * @var array
     */
    private array $aExtensions = [];

    /**
     * The constructor
     *
     * @param mixed $xRenderer
     */
    public function __construct(private Twig $xRenderer)
    {}

    /**
     * Add a namespace to this view renderer
     *
     * @param string        $sNamespace         The namespace name
     * @param string        $sDirectory         The namespace directory
     * @param string        $sExtension         The extension to append to template names
     *
     * @return void
     */
    public function addNamespace(string $sNamespace, string $sDirectory, string $sExtension = ''): void
    {
        $this->aExtensions[$sNamespace] = '.' . ltrim($sExtension, '.');
        $this->xRenderer->getLoader()->addPath($sDirectory, $sNamespace);
    }

    /**
     * Render a view
     *
     * @param Store         $store        A store populated with the view data
     *
     * @return string
     */
    public function render(Store $store): string
    {
        $sNamespace = $store->getNamespace();
        $sViewName = !$sNamespace || $sNamespace === 'slim' ? $store->getViewName() :
            '@' . $sNamespace . '/' . $store->getViewName();
        $sViewName = str_replace('.', '/', $sViewName);
        if(isset($this->aExtensions[$sNamespace]))
        {
            $sViewName .= $this->aExtensions[$sNamespace];
        }

        // Render the template
        return trim((string)$this->xRenderer->fetch($sViewName, $store->getViewData()), " \t\n");
    }
}
