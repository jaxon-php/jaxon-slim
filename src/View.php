<?php

namespace Jaxon\Slim;

use Jaxon\App\View\ViewInterface;
use Jaxon\App\View\Store;

use function trim;

class View implements ViewInterface
{
    /**
     * @var array
     */
    protected $aNamespaces;

    /**
     * @var mixed
     */
    protected $xView;

    /**
     * The constructor
     *
     * @param mixed $xView
     */
    public function __construct($xView)
    {
        $this->xView = $xView;
    }

    /**
     * Add a namespace to this view renderer
     *
     * @param string        $sNamespace         The namespace name
     * @param string        $sDirectory         The namespace directory
     * @param string        $sExtension         The extension to append to template names
     *
     * @return void
     */
    public function addNamespace(string $sNamespace, string $sDirectory, string $sExtension = '')
    {
        $this->aNamespaces[$sNamespace] = ['extension' => $sExtension];
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
        // Render the template
        $sViewName = $store->getViewName() . ($this->aNamespaces[$store->getNamespace()]['extension'] ?? '');
        return trim((string)$this->xView->fetch($sViewName, $store->getViewData()), " \t\n");
    }
}
