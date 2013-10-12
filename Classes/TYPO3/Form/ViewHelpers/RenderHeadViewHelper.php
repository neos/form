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

/**
 * Output the configured stylesheets and JavaScript include tags for a given preset
 */
class RenderHeadViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Form\Factory\ArrayFormFactory
	 */
	protected $formBuilderFactory;

	/**
	 * @param string $presetName name of the preset to use
	 * @return string the rendered form head
	 */
	public function render($presetName = 'default') {
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
	 */
	protected function resolveResourcePath($resourcePath) {
		// TODO: This method should be somewhere in the resource manager probably?
		$matches = array();
		preg_match('#resource://([^/]*)/Public/(.*)#', $resourcePath, $matches);
		if ($matches === array()) {
			throw new \TYPO3\Fluid\Core\ViewHelper\Exception('Resource path "' . $resourcePath . '" can\'t be resolved.', 1328543327);
		}
		$package = $matches[1];
		$path = $matches[2];
		return $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'Packages/' . $package . '/' . $path;
	}
}
