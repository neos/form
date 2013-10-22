<?php
namespace TYPO3\Form\Tests\Functional\Fixtures;

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
 * Form element that amends itself with another field having a validator
 */
class TestingFormElementWithSubElements extends \TYPO3\Form\FormElements\Section {

	/**
	 * This is a callback that is invoked by the Form Factory after the whole form has been built.
	 *
	 * @return void
	 * @api
	 */
	public function onBuildingFinished() {
		$element = $this->createElement('myInteger', 'TYPO3.Form:SingleLineText');
		$element->addValidator(new \TYPO3\Flow\Validation\Validator\IntegerValidator());
	}
}
