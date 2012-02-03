<?php
namespace TYPO3\Form\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @todo document, rename?, move to TYPO3.FormBuilder?
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
	public function render($presetName = 'Default') {
		$content = '';
		$presetConfiguration = $this->formBuilderFactory->getPresetConfiguration($presetName);
		$cssFiles = isset($presetConfiguration['cssFiles']) ? $this->resolveCssFiles($presetConfiguration['cssFiles']) : array();
		foreach ($cssFiles as $cssFile) {
			$content .= sprintf('<link href="%s" rel="stylesheet">', $cssFile);
		}
		return $content;
	}

	protected function resolveCssFiles(array $cssFiles) {
		$processedCssFiles = array();
		foreach ($cssFiles as $cssFile) {
			// TODO: This method should be somewhere in the resource manager probably?
			if (preg_match('#resource://([^/]*)/Public/(.*)#', $cssFile, $matches) > 0) {
				$package = $matches[1];
				$path = $matches[2];

				$processedCssFiles[] = $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'Packages/' . $package . '/' . $path;

			} else {
				$processedCssFiles[] = $cssFile;
			}
		}
		return $processedCssFiles;
	}
}
?>