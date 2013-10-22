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
abstract class AbstractFunctionalTestCase extends \TYPO3\Flow\Tests\FunctionalTestCase {

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
	 * Go to the next form page
	 *
	 * @param \Symfony\Component\DomCrawler\Form $form
	 * @return \TYPO3\Flow\Http\Response
	 */
	protected function gotoNextFormPage(\Symfony\Component\DomCrawler\Form $form) {
		$nextButton = $this->browser->getCrawler()->filterXPath('//nav[@class="form-navigation"]/*/*[contains(@class, "next")]/button');
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
		$previousButton = $this->browser->getCrawler()->filterXPath('//nav[@class="form-navigation"]/*/*[contains(@class, "previous")]/button');
		$previousButton->rewind();
		$form->set(new InputFormField($previousButton->current()));

		return $this->browser->submit($form);
	}
}
