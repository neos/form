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

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper\Exception as ViewHelperException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;
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
        $stylesheets = isset($presetConfiguration['stylesheets']) ? $presetConfiguration['stylesheets'] : [];
        foreach ($stylesheets as $stylesheet) {
            $content .= sprintf('<link href="%s" rel="stylesheet">', $this->resourceManager->getPublicPackageResourceUriByPath($stylesheet['source']));
        }
        $javaScripts = isset($presetConfiguration['javaScripts']) ? $presetConfiguration['javaScripts'] : [];
        foreach ($javaScripts as $javaScript) {
            $content .= sprintf('<script src="%s"></script>', $this->resourceManager->getPublicPackageResourceUriByPath($javaScript['source']));
        }
        return $content;
    }
}
