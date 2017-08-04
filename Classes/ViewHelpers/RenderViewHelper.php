<?php
namespace Neos\Form\ViewHelpers;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Response;
use Neos\Utility\Arrays;
use Neos\Form\Persistence\FormPersistenceManagerInterface;

/**
 * Main Entry Point to render a Form into a Fluid Template
 *
 * <pre>
 * {namespace form=Neos\Form\ViewHelpers}
 * <form:render factoryClass="NameOfYourCustomFactoryClass" />
 * </pre>
 *
 * The factory class must implement {@link Neos\Form\Factory\FormFactoryInterface}.
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
     * @param string $factoryClass The fully qualified class name of the factory (which has to implement \Neos\Form\Factory\FormFactoryInterface)
     * @param string $presetName name of the preset to use
     * @param array $overrideConfiguration factory specific configuration
     * @return string the rendered form
     */
    public function render($persistenceIdentifier = null, $factoryClass = \Neos\Form\Factory\ArrayFormFactory::class, $presetName = 'default', array $overrideConfiguration = null)
    {
        if (isset($persistenceIdentifier)) {
            $overrideConfiguration = Arrays::arrayMergeRecursiveOverrule($this->formPersistenceManager->load($persistenceIdentifier), $overrideConfiguration ?: []);
        }

        $factory = $this->objectManager->get($factoryClass);
        $formDefinition = $factory->build($overrideConfiguration, $presetName);
        $response = new Response($this->controllerContext->getResponse());
        $form = $formDefinition->bind($this->controllerContext->getRequest(), $response);
        return $form->render();
    }
}
