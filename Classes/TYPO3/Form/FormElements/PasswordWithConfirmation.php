<?php
namespace TYPO3\Form\FormElements;

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
 * A password with confirmation form element
 */
class PasswordWithConfirmation extends \TYPO3\Form\Core\Model\AbstractFormElement {

	public function onSubmit(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime, &$elementValue) {
		if ($elementValue['password'] !== $elementValue['confirmation']) {
			$processingRule = $this->getRootForm()->getProcessingRule($this->getIdentifier());
			$processingRule->getProcessingMessages()->addError(new \TYPO3\Flow\Error\Error('Password doesn\'t match confirmation', 1334768052));
		}
		$elementValue = $elementValue['password'];
	}

}
