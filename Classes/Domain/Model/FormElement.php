<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Form element
 *
 * @FLOW3\Entity
 */
class FormElement {

	/**
	 * The identifier
	 * @var string
	 */
	protected $identifier;

	/**
	 * The parent page
	 * @var \TYPO3\Form\Domain\Model\Page
	 */
	protected $parentPage;

	/**
	 * Constructor. Needs this FormElement's identifier
	 *
	 * @param string $identifier The FormElement's identifier
	 * @return void
	 * @api
	 */
	public function __construct($identifier) {
		$this->identifier = $identifier;
	}
	/**
	 * Get the Form element's identifier
	 *
	 * @return string The Form element's identifier
	 * @api
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Get the Form element's parent page
	 *
	 * @return \TYPO3\Form\Domain\Model\Page The Form element's parent page
	 * @internal
	 */
	public function getParentPage() {
		return $this->parentPage;
	}

	/**
	 * Sets this Form element's parent page
	 *
	 * @param \TYPO3\Form\Domain\Model\Page $parentPage The Form element's parent page
	 * @return void
	 * @internal
	 */
	public function setParentPage(Page $parentPage) {
		$this->parentPage = $parentPage;
	}

}
?>