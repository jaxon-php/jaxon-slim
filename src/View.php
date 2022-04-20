<?php

namespace Jaxon\Slim;

use Jaxon\App\View\ViewInterface;
use Jaxon\App\View\Store;
use Psr\Http\Message\ResponseInterface;

use function trim;

class View implements ViewInterface
{
    /**
     * @var mixed
     */
    protected $xView;

    /**
     * @var ResponseInterface
     */
    protected $xResponse;

    /**
     * The constructor
     *
     * @param mixed $xView
     * @param ResponseInterface $xResponse
     */
    public function __construct($xView, ResponseInterface $xResponse)
    {
        $this->xView = $xView;
        $this->xResponse = $xResponse;
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
    {}

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
        return trim($this->xView->render($this->xResponse, $store->getViewName(), $store->getViewData()), " \t\n");
    }
}
