<?php
namespace TYPO3\Form\Tests\Functional\Fixtures\Controller;

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
 * Controller for rendering a form defined in
 */
class FormController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * render the form identified by $formFactoryClassName
	 *
	 * @param string $formFactoryClassName
	 */
	public function indexAction($formFactoryClassName) {
		$formFactoryClassName = 'TYPO3\Form\Tests\Functional\Fixtures\FormFactories\\' . $formFactoryClassName . 'Factory';
		/* @var $formFactory \TYPO3\Form\Factory\FormFactoryInterface */
		$formFactory = new $formFactoryClassName();
		$formDefinition = $formFactory->build(array(), 'default');

		$formRuntime = $formDefinition->bind($this->request, $this->response);

		return $formRuntime->render();
	}
}
