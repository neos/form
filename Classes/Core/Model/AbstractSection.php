<?php
namespace Neos\Form\Core\Model;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Form\Core\Model\Renderable\AbstractRenderable;
use Neos\Form\Core\Model\Renderable\RenderableInterface;
use Neos\Form\Core\Runtime\FormRuntime;
use Neos\Form\Exception\FormDefinitionConsistencyException;
use Neos\Form\Exception\IdentifierNotValidException;
use Neos\Form\Exception\TypeDefinitionNotFoundException;
use Neos\Form\Exception\TypeDefinitionNotValidException;

/**
 * A base class for "section-like" form parts like "Page" or "Section" (which
 * is rendered as "Fieldset")
 *
 * **This class should not be subclassed by developers**, it is only
 * used for improving the internal code structure.
 *
 * This class contains multiple FormElements ({@link FormElementInterface}).
 *
 * Please see {@link FormDefinition} for an in-depth explanation.
 */
abstract class AbstractSection extends Renderable\AbstractCompositeRenderable
{
    /**
     * The identifier of this Section
     *
     * @var string
     */
    protected $identifier;

    /**
     * Abstract "type" of this Section
     *
     * @var string
     */
    protected $type;

    /**
     * Form Elements of this Section
     *
     * @var FormElementInterface[]
     * @api
     */
    protected $renderables = [];

    /**
     * Constructor. Needs the identifier and type of this element
     *
     * @param string $identifier The Section identifier
     * @param string $type The Section type
     * @throws IdentifierNotValidException if the identifier was no non-empty string
     * @api
     */
    public function __construct($identifier, $type)
    {
        if (!is_string($identifier) || strlen($identifier) === 0) {
            throw new IdentifierNotValidException('The given identifier was not a string or the string was empty.', 1325574803);
        }

        $this->identifier = $identifier;
        $this->type = $type;
    }

    /**
     * Get the child Form Elements
     *
     * @return FormElementInterface[] The Page's elements
     * @api
     */
    public function getElements()
    {
        return $this->renderables;
    }

    /**
     * Get the child Form Elements
     *
     * @return RenderableInterface[] The Page's elements
     * @api
     */
    public function getElementsRecursively()
    {
        return $this->getRenderablesRecursively();
    }

    /**
     * Add a new form element at the end of the section
     *
     * @param FormElementInterface $formElement The form element to add
     * @return void
     * @throws FormDefinitionConsistencyException if FormElement is already added to a section
     * @api
     */
    public function addElement(FormElementInterface $formElement)
    {
        $this->addRenderable($formElement);
    }

    /**
     * Create a form element with the given $identifier and attach it to this section/page.
     *
     * - Create Form Element object based on the given $typeName
     * - set defaults inside the Form Element (based on the parent form's field defaults)
     * - attach Form Element to this Section/Page
     * - return the newly created Form Element object
     *
     *
     * @param string $identifier Identifier of the new form element
     * @param string $typeName type of the new form element
     * @return FormElementInterface the newly created form element
     * @throws TypeDefinitionNotFoundException
     * @throws TypeDefinitionNotValidException
     * @api
     */
    public function createElement($identifier, $typeName)
    {
        $formDefinition = $this->getRootForm();

        try {
            $typeDefinition = $formDefinition->getFormFieldTypeManager()->getMergedTypeDefinition($typeName);
        } catch (TypeDefinitionNotFoundException $exception) {
            $element = new UnknownFormElement($identifier, $typeName);
            $this->addElement($element);
            return $element;
        }
        if (!isset($typeDefinition['implementationClassName'])) {
            throw new TypeDefinitionNotFoundException(sprintf('The "implementationClassName" was not set in type definition "%s".', $typeName), 1325689855);
        }
        $implementationClassName = $typeDefinition['implementationClassName'];
        $element = new $implementationClassName($identifier, $typeName);
        if (!$element instanceof FormElementInterface) {
            throw new TypeDefinitionNotValidException(sprintf('The "implementationClassName" for element "%s" ("%s") does not implement the FormElementInterface.', $identifier, $implementationClassName), 1327318156);
        }
        unset($typeDefinition['implementationClassName']);

        $this->addElement($element);
        if ($element instanceof AbstractRenderable) {
            $element->setOptions($typeDefinition);
        }

        $element->initializeFormElement();
        return $element;
    }

    /**
     * Move FormElement $element before $referenceElement.
     *
     * Both $element and $referenceElement must be direct descendants of this Section/Page.
     *
     * @param FormElementInterface $elementToMove
     * @param FormElementInterface $referenceElement
     * @return void
     * @api
     */
    public function moveElementBefore(FormElementInterface $elementToMove, FormElementInterface $referenceElement)
    {
        $this->moveRenderableBefore($elementToMove, $referenceElement);
    }

    /**
     * Move FormElement $element after $referenceElement
     *
     * Both $element and $referenceElement must be direct descendants of this Section/Page.
     *
     * @param FormElementInterface $elementToMove
     * @param FormElementInterface $referenceElement
     * @return void
     * @api
     */
    public function moveElementAfter(FormElementInterface $elementToMove, FormElementInterface $referenceElement)
    {
        $this->moveRenderableAfter($elementToMove, $referenceElement);
    }

    /**
     * Remove $elementToRemove from this Section/Page
     *
     * @param FormElementInterface $elementToRemove
     * @return void
     * @api
     */
    public function removeElement(FormElementInterface $elementToRemove)
    {
        $this->removeRenderable($elementToRemove);
    }

    /**
     * This callback is invoked by the FormRuntime whenever values are mapped and validated
     * (after a form page was submitted)
     * @see \Neos\Form\Core\Runtime\FormRuntime::mapAndValidate()
     *
     * @param FormRuntime $formRuntime
     * @param mixed $elementValue submitted value of the element *before post processing*
     * @return void
     * @api
     */
    public function onSubmit(FormRuntime $formRuntime, &$elementValue)
    {
    }
}
