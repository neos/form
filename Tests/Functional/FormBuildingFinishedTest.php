<?php
namespace TYPO3\Form\Tests\Functional;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */
use Symfony\Component\DomCrawler\Field\InputFormField;

/**
 * Testcase for onBuildingFinished
 *
 * @group large
 */
class FormBuildingFinishedTest extends AbstractFunctionalTestCase {

	/**
	 * @test
	 */
	public function aFormElementCanAddNewSubelementsWithValidationApplied() {
		$this->browser->request('http://localhost/test/form/simpleform/TestingFormBuildingFinished');

		$form = $this->browser->getForm();
		$form['--testing']['myInteger']->setValue('no int');

		$this->gotoNextFormPage($form);
		$this->assertSame('no int', $form['--testing']['myInteger']->getValue());
		$this->assertSame(' error', $this->browser->getCrawler()->filterXPath('//*[contains(@class,"error")]//input[@id="testing-myInteger"]')->attr('class'));

		$this->browser->getForm();
	}
}
