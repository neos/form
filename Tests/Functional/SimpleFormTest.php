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
 * Testcase for Simple Form
 *
 * @group large
 */
class SimpleFormTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var \TYPO3\Flow\Http\Client\Browser
	 */
	protected $browser;

	/**
	 * Initializer
	 */
	public function setUp() {
		parent::setUp();

		$route = new \TYPO3\Flow\Mvc\Routing\Route();
		$route->setUriPattern('test/form/simpleform/{formFactoryClassName}');
		$route->setDefaults(array(
			'@package' => 'TYPO3.Form',
			'@subpackage' => 'Tests\Functional\Fixtures',
			'@controller' => 'Form',
			'@action' => 'index',
			'@format' => 'html'
		));
		$route->setAppendExceedingArguments(TRUE);
		$this->router->addRoute($route);
	}

	/**
	 * @test
	 */
	public function goingForthAndBackStoresFormValuesOfFirstPage() {
		$this->browser->request('http://localhost/test/form/simpleform/ThreePageFormWithValidation');

		$form = $this->browser->getForm();
		$form['--three-page-form-with-validation']['text1-1']->setValue('My Text on the first page');

		$this->gotoNextFormPage($form);

		$this->gotoPreviousFormPage($this->browser->getForm());

		$form = $this->browser->getForm();
		$this->assertSame('My Text on the first page', $form['--three-page-form-with-validation']['text1-1']->getValue());
	}

	/**
	 * @test
	 */
	public function goingForthAndBackStoresFormValuesOfSecondPage() {
		$this->browser->request('http://localhost/test/form/simpleform/ThreePageFormWithValidation');

		$this->gotoNextFormPage($this->browser->getForm());

		$form = $this->browser->getForm();
		$form['--three-page-form-with-validation']['text2-1']->setValue('My Text on the second page');
		$this->gotoPreviousFormPage($form);
		$this->gotoNextFormPage($this->browser->getForm());

		$form = $this->browser->getForm();
		$this->assertSame('My Text on the second page', $form['--three-page-form-with-validation']['text2-1']->getValue());
	}

	/**
	 * @test
	 */
	public function goingForthAndBackStoresFormValuesOfSecondPageAndTriggersValidationOnlyWhenGoingForward() {
		$this->browser->request('http://localhost/test/form/simpleform/ThreePageFormWithValidation');

		$this->gotoNextFormPage($this->browser->getForm());

		$form = $this->browser->getForm();
		$form['--three-page-form-with-validation']['text2-1']->setValue('My Text on the second page');
		$this->gotoPreviousFormPage($form);
		$this->gotoNextFormPage($this->browser->getForm());
		$r = $this->gotoNextFormPage($this->browser->getForm());

		$this->assertSame(' error', $this->browser->getCrawler()->filterXPath('//*[contains(@class,"error")]//input[@id="three-page-form-with-validation-text2-1"]')->attr('class'));
		$form = $this->browser->getForm();
		$form['--three-page-form-with-validation']['text2-1']->setValue('42');
		$this->gotoNextFormPage($form);

		$form = $this->browser->getForm();
		$this->assertSame('', $form['--three-page-form-with-validation']['text3-1']->getValue());
	}

	/**
	 * Go to the next form page
	 *
	 * @param \Symfony\Component\DomCrawler\Form $form
	 * @return \TYPO3\Flow\Http\Response
	 */
	protected function gotoNextFormPage(\Symfony\Component\DomCrawler\Form $form) {
		$nextButton = $this->browser->getCrawler()->filterXPath('//nav[@class="form-navigation"]/*/*[@class="next"]/button');
		$nextButton->rewind();
		$form->set(new InputFormField($nextButton->current()));

		return $this->browser->submit($form);
	}

	/**
	 * Go to the previous form page
	 *
	 * @param \Symfony\Component\DomCrawler\Form $form
	 * @return \TYPO3\Flow\Http\Response
	 */
	protected function gotoPreviousFormPage(\Symfony\Component\DomCrawler\Form $form) {
		$previousButton = $this->browser->getCrawler()->filterXPath('//nav[@class="form-navigation"]/*/*[@class="previous"]/button');
		$previousButton->rewind();
		$form->set(new InputFormField($previousButton->current()));

		return $this->browser->submit($form);
	}
}
