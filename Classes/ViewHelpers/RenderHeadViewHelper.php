<?php
namespace Neos\Form\ViewHelpers;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\Form\Factory\ArrayFormFactory;

/**
 * Output the configured stylesheets and JavaScript include tags for a given preset
 */
class RenderHeadViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var ArrayFormFactory
     */
    protected $formBuilderFactory;

    /**
     * Initialize the arguments.
     *
     * @return void
     * @throws \Neos\FluidAdaptor\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('presetName', 'string', 'Relative Fusion path to be rendered', false, ' default');
    }

    /**
     * @return string the rendered form head
     * @throws \Neos\Form\Exception\PresetNotFoundException
     */
    public function render(): string
    {
        $content = '';
        $presetConfiguration = $this->formBuilderFactory->getPresetConfiguration($this->arguments['presetName']);
        $stylesheets = $presetConfiguration['stylesheets'] ?? [];
        foreach ($stylesheets as $stylesheet) {
            $content .= sprintf('<link href="%s" rel="stylesheet">', $this->resourceManager->getPublicPackageResourceUriByPath($stylesheet['source']));
        }
        $javaScripts = $presetConfiguration['javaScripts'] ?? [];
        foreach ($javaScripts as $javaScript) {
            $content .= sprintf('<script src="%s"></script>', $this->resourceManager->getPublicPackageResourceUriByPath($javaScript['source']));
        }
        return $content;
    }
}
