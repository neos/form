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
class FormState {

	protected $lastDisplayedPage = 0;

	public function getLastDisplayedPage() {
		return $this->lastDisplayedPage;
	}

	public function setLastDisplayedPage($lastDisplayedPage) {
		$this->lastDisplayedPage = $lastDisplayedPage;
	}
}
?>