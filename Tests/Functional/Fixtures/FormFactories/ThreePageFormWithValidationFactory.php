<?php
namespace TYPO3\Form\Tests\Functional\Fixtures\FormFactories;

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
 * Basic three-page form
 */
class ThreePageFormWithValidationFactory extends \TYPO3\Form\Factory\AbstractFormFactory {

	public function build(array $configuration, $presetName) {
		$formDefinition = new \TYPO3\Form\Core\Model\FormDefinition('three-page-form-with-validation', $this->getPresetConfiguration($presetName));

		$page1 = $formDefinition->createPage('page1');
		$page2 = $formDefinition->createPage('page2');
		$page3 = $formDefinition->createPage('page3');

		$page1->createElement('text1-1', 'TYPO3.Form:SingleLineText');
		$text21 = $page2->createElement('text2-1', 'TYPO3.Form:SingleLineText');
		$text21->addValidator(new \TYPO3\Flow\Validation\Validator\IntegerValidator());
		$page3->createElement('text3-1', 'TYPO3.Form:SingleLineText');

		return $formDefinition;
	}
}
