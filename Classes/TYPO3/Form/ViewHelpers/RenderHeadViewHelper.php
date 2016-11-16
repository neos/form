<?php
namespace TYPO3\Form\ViewHelpers;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Resource\Publishing\ResourcePublisher;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\Exception as ViewHelperException;
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
     * @var ResourcePublisher
     */
    protected $resourcePublisher;

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
            $content .= sprintf('<link href="%s" rel="stylesheet">', $this->resolveResourcePath($stylesheet['source']));
        }
        $javaScripts = isset($presetConfiguration['javaScripts']) ? $presetConfiguration['javaScripts'] : array();
        foreach ($javaScripts as $javaScript) {
            $content .= sprintf('<script src="%s"></script>', $this->resolveResourcePath($javaScript['source']));
        }
        return $content;
    }

    /**
     * @param string $resourcePath
     * @return string
     * @throws ViewHelperException
     */
    protected function resolveResourcePath($resourcePath)
    {
        // TODO: This method should be somewhere in the resource manager probably?
        $matches = array();
        preg_match('#resource://([^/]*)/Public/(.*)#', $resourcePath, $matches);
        if ($matches === array()) {
            throw new ViewHelperException('Resource path "' . $resourcePath . '" can\'t be resolved.', 1328543327);
        }
        $package = $matches[1];
        $path = $matches[2];
        return $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'Packages/' . $package . '/' . $path;
    }
}
