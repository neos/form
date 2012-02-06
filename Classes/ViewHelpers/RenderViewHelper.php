<?php
namespace TYPO3\Form\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Main Entry Point to render a Form into a Fluid Template
 *
 * Usage
 * =====
 *
 * <pre>
 * {namespace form=TYPO3\Form\ViewHelpers}
 * <form:render factoryClass="NameOfYourCustomFactoryClass" />
 * </pre>
 *
 * The factory class must implement {@link TYPO3\Form\Factory\FormFactoryInterface}.
 *
 * @api
 */
class RenderViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Form\Persistence\FormPersistenceManagerInterface
	 */
	protected $formPersistenceManager;

	/**
	 * @param string $persistenceIdentifier the persistence identifier for the form.
	 * @param string $factoryClass The fully qualified class name of the factory (which has to implement \TYPO3\Form\Factory\FormFactoryInterface)
	 * @param string $presetName name of the preset to use
	 * @param array $overrideConfiguration factory specific configuration
	 * @return string the rendered form
	 */
	public function render($persistenceIdentifier = NULL, $factoryClass = 'TYPO3\Form\Factory\ArrayFormFactory', $presetName = 'default', array $overrideConfiguration = array()) {
		if (isset($persistenceIdentifier)) {
			$overrideConfiguration = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($this->formPersistenceManager->load($persistenceIdentifier), $overrideConfiguration);
		}

		$factory = $this->objectManager->get($factoryClass);
		$formDefinition = $factory->build($overrideConfiguration, $presetName);
		$form = $formDefinition->bind($this->controllerContext->getRequest());
		return $form->render();
	}
}
?>