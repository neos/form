<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A Form
 */
class Form {

	/**
	 * The identifier
	 * @var string
	 */
	protected $identifier;

	/**
	 * The pages
	 * @var array<TYPO3\Form\Domain\Model\Page>
	 */
	protected $pages = array();

	/**
	 * Constructor. Needs this Form's identifier
	 *
	 * @param string $identifier The Form's identifier
	 * @return void
	 * @api
	 */
	public function __construct($identifier) {
		$this->identifier = $identifier;
	}

	/**
	 * Get the Form's identifier
	 *
	 * @return string The Form's identifier
	 * @api
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Get the Form's pages
	 *
	 * @return array<TYPO3\Form\Domain\Model\Page> The Form's pages
	 * @api
	 */
	public function getPages() {
		return $this->pages;
	}

	/**
	 * Add a new page at the end of the form
	 *
	 * @param Page $page
	 * @api
	 */
	public function addPage(Page $page) {
		$this->pages[] = $page;
		$page->setParentForm($this);
	}
}
?>