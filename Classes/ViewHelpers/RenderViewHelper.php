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

use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\ActionResponse;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Response;
use Neos\FluidAdaptor\Core\ViewHelper\Exception as ViewHelperException;
use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Core\Model\Renderable\RootRenderableInterface;
use Neos\Form\Core\Runtime\FormRuntime;
use Neos\Form\Factory\ArrayFormFactory;
use Neos\Form\Factory\FormFactoryInterface;
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
     * Initialize the arguments.
     *
     * @return void
     * @throws \Neos\FluidAdaptor\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('persistenceIdentifier', 'string', 'The persistence identifier for the form');
        $this->registerArgument('factoryClass', 'string', 'The fully qualified class name of the factory (which has to implement \Neos\Form\Factory\FormFactoryInterface)', false, ArrayFormFactory::class);
        $this->registerArgument('presetName', 'string', 'Name of the preset to use', false, 'default');
        $this->registerArgument('overrideConfiguration', 'array', 'Factory specific configuration', false, []);
    }

    /**
     * @return string the rendered form
     * @throws ViewHelperException
     * @throws \Neos\Form\Exception\RenderingException
     */
    public function render(): string
    {
        if ($this->hasArgument('persistenceIdentifier')) {
            $overrideConfiguration = Arrays::arrayMergeRecursiveOverrule($this->formPersistenceManager->load($this->arguments['persistenceIdentifier']), $this->arguments['overrideConfiguration']);
        } else {
            $overrideConfiguration = $this->arguments['overrideConfiguration'];
        }

        $factory = $this->objectManager->get($this->arguments['factoryClass']);
        $formDefinition = $factory->build($overrideConfiguration, $this->arguments['presetName']);
        if (!$formDefinition instanceof FormDefinition) {
            throw new ViewHelperException(sprintf('The factory method %s::build() has to return an instance of FormDefinition, got "%s"', $this->arguments['factoryClass'], is_object($formDefinition) ? get_class($formDefinition) : gettype($formDefinition)), 1504024351);
        }
        $form = $formDefinition->bind($this->controllerContext->getRequest(), $this->controllerContext->getResponse());
        return $form->render();
    }
}
