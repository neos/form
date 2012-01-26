<?php
namespace TYPO3\Form\ViewHelpers\Uri;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @todo document
 */
class ImageViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\FLOW3\Resource\Publishing\ResourcePublisher
	 * @FLOW3\Inject
	 */
	protected $resourcePublisher;

	/**
	 * Resizes a given image (if required) and returns the URI
	 *
	 * @param \TYPO3\Media\Domain\Model\Image $image
	 * @param integer $maxWidth maximum width of the image
	 * @param integer $maxHeight maximum height of the image
	 * @param boolean $absolute whether or not to create an absolute URI
	 *
	 * @return string image URI
	 */
	public function render(\TYPO3\Media\Domain\Model\Image $image = NULL, $maxWidth = NULL, $maxHeight = NULL, $absolute = FALSE) {
		$thumbnail = $image->getThumbnail($maxWidth, $maxHeight);
		$thumbnailUri = $this->resourcePublisher->getPersistentResourceWebUri($thumbnail->getResource());
		if ($absolute) {
			$thumbnailUri = $this->renderingContext->getControllerContext()->getRequest()->getBaseUri() . $thumbnailUri;
		}
		return $thumbnailUri;
	}
}

?>