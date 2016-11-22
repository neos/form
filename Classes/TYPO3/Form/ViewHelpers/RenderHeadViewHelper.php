<?php
namespace TYPO3\Form\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper\Exception as ViewHelperException;
use TYPO3\Flow\Resource\ResourceManager;
use TYPO3\Form\Factory\ArrayFormFactory;

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
     * @param string $presetName name of the preset to use
     * @return string the rendered form head
     */
    public function render($presetName = 'default')
    {
        $content = '';
        $presetConfiguration = $this->formBuilderFactory->getPresetConfiguration($presetName);
        $stylesheets = isset($presetConfiguration['stylesheets']) ? $presetConfiguration['stylesheets'] : array();
        foreach ($stylesheets as $stylesheet) {
            $content .= sprintf('<link href="%s" rel="stylesheet">', $this->resourceManager->getPublicPackageResourceUriByPath($stylesheet['source']));
        }
        $javaScripts = isset($presetConfiguration['javaScripts']) ? $presetConfiguration['javaScripts'] : array();
        foreach ($javaScripts as $javaScript) {
            $content .= sprintf('<script src="%s"></script>', $this->resourceManager->getPublicPackageResourceUriByPath($javaScript['source']));
        }
        return $content;
    }
}
