<?php

namespace Jaxon\Slim;

use Jaxon\App\View\Store;
use Jaxon\App\View\ViewInterface;
use Jaxon\App\View\ViewTrait;
use Slim\Views\PhpRenderer;

use function strlen;
use function substr;
use function trim;

class PhpView implements ViewInterface
{
    use ViewTrait;

    /**
     * The constructor
     *
     * @param PhpRenderer $xRenderer
     */
    public function __construct(private PhpRenderer $xRenderer)
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
        $sViewName = $store->getViewName();
        $sNamespace = $store->getNamespace();
        // For this view renderer, the view name doesn't need to be prepended with the namespace.
        $nNsLen = strlen($sNamespace) + 2;
        if(substr($sViewName, 0, $nNsLen) === $sNamespace . '::')
        {
            $sViewName = substr($sViewName, $nNsLen);
        }

        // View namespace
        $this->setCurrentNamespace($sNamespace);

        // Render the template
        $sTemplateFile = $this->sDirectory . $sViewName . $this->sExtension;
        return trim((string)$this->xRenderer->fetch($sTemplateFile, $store->getViewData()), " \t\n");
    }
}
