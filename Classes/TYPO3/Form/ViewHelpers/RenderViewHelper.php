<?php
namespace TYPO3\Form\ViewHelpers;

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
use TYPO3\Flow\Http\Response;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Form\Persistence\FormPersistenceManagerInterface;

/**
 * Main Entry Point to render a Form into a Fluid Template
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
class RenderViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @Flow\Inject
     * @var FormPersistenceManagerInterface
     */
    protected $formPersistenceManager;

    /**
     * @param string $persistenceIdentifier the persistence identifier for the form.
     * @param string $factoryClass The fully qualified class name of the factory (which has to implement \TYPO3\Form\Factory\FormFactoryInterface)
     * @param string $presetName name of the preset to use
     * @param array $overrideConfiguration factory specific configuration
     * @return string the rendered form
     */
    public function render($persistenceIdentifier = null, $factoryClass = 'TYPO3\Form\Factory\ArrayFormFactory', $presetName = 'default', array $overrideConfiguration = null)
    {
        if (isset($persistenceIdentifier)) {
            $overrideConfiguration = Arrays::arrayMergeRecursiveOverrule($this->formPersistenceManager->load($persistenceIdentifier), $overrideConfiguration ?: array());
        }

        $factory = $this->objectManager->get($factoryClass);
        $formDefinition = $factory->build($overrideConfiguration, $presetName);
        $response = new Response($this->controllerContext->getResponse());
        $form = $formDefinition->bind($this->controllerContext->getRequest(), $response);
        return $form->render();
    }
}
