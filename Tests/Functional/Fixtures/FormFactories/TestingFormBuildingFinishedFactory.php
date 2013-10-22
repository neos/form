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
 * Simple form for testing
 */
class TestingFormBuildingFinishedFactory extends \TYPO3\Form\Factory\ArrayFormFactory {

	public function build(array $configuration, $presetName) {
		$configuration = array(
			'type' => 'TYPO3.Form:Form',
			'identifier' => 'testing',
			'label' => 'My Label',
			'renderables' => array(
				array(
					'type' => 'TYPO3.Form:Page',
					'identifier' => 'general',
					'renderables' => array(
						array(
							'type' => 'TYPO3.Form:TestingFormElementWithSubElements',
							'identifier' => 'subel',
						)
					)
				)
			)
		);
		return parent::build($configuration, $presetName);
	}
}
