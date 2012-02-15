<?php
namespace TYPO3\Form\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Output the configured stylesheets and JavaScript include tags for a given preset
 */
class RenderHeadViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * @FLOW3\Inject
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
?>