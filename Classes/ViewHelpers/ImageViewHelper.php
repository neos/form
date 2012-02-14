<?php
namespace TYPO3\Form\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @todo document / replace with core Image VH if available
 */
class ImageViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	/**
	 * @var \TYPO3\FLOW3\Resource\Publishing\ResourcePublisher
	 * @FLOW3\Inject
	 */
	protected $resourcePublisher;

	/**
	 * @var string
	 */
	protected $tagName = 'img';

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('alt', 'string', 'Specifies an alternate text for an image', TRUE);
		$this->registerTagAttribute('ismap', 'string', 'Specifies an image as a server-side image-map. Rarely used. Look at usemap instead', FALSE);
		$this->registerTagAttribute('usemap', 'string', 'Specifies an image as a client-side image-map', FALSE);
	}

	/**
	 * Resizes a given image (if required) and renders the respective img tag
	 *
	 * @param \TYPO3\Media\Domain\Model\Image $image
	 * @param integer $maxWidth maximum width of the image
	 * @param integer $maxHeight maximum height of the image
	 *
	 * @return string rendered tag.
	 */
	public function render(\TYPO3\Media\Domain\Model\Image $image = NULL, $maxWidth = NULL, $maxHeight = NULL) {
		$thumbnail = $image->getThumbnail($maxWidth, $maxHeight);
		$thumbnailUri = $this->resourcePublisher->getPersistentResourceWebUri($thumbnail->getResource());
		$this->tag->addAttribute('src', $thumbnailUri);
		$this->tag->addAttribute('width', $thumbnail->getWidth());
		$this->tag->addAttribute('height', $thumbnail->getHeight());

		return $this->tag->render();
	}
}

?>