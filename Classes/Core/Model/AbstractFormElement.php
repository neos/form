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

use Neos\Flow\Validation\Validator\NotEmptyValidator;
use Neos\Form\Core\Runtime\FormRuntime;
use Neos\Form\Exception\IdentifierNotValidException;

/**
 * A base form element, which is the starting point for creating custom (PHP-based)
 * Form Elements.
 *
 * **This class is meant to be subclassed by developers.**
 *
 * A *FormElement* is a part of a *Page*, which in turn is part of a FormDefinition.
 * See {@link FormDefinition} for an in-depth explanation.
 *
 * Subclassing this class is a good starting-point for implementing custom PHP-based
 * Form Elements.
 *
 * Most of the functionality and API is implemented in {@link \Neos\Form\Core\Model\Renderable\AbstractRenderable}, so
 * make sure to check out this class as well.
 *
 * Still, it is quite rare that you need to subclass this class; often
 * you can just use the {@link \Neos\Form\FormElements\GenericFormElement} and replace some templates.
 */
abstract class AbstractFormElement extends Renderable\AbstractRenderable implements FormElementInterface
{
    /**
     * The identifier of this Form Element
     *
     * @var string
     */
    protected $identifier;

    /**
     * Abstract "type" of this Form Element
     *
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * Constructor. Needs this FormElement's identifier and the FormElement type
     *
     * @param string $identifier The FormElement's identifier
     * @param string $type The Form Element Type
     * @api
     * @throws IdentifierNotValidException
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
     * Override this method in your custom FormElements if needed
     *
     * @return void
     */
    public function initializeFormElement()
    {
    }

    /**
     * Get the global unique identifier of the element
     *
     * @return string
     */
    public function getUniqueIdentifier()
    {
        $formDefinition = $this->getRootForm();
        $uniqueIdentifier = sprintf('%s-%s', $formDefinition->getIdentifier(), $this->identifier);
        $uniqueIdentifier = preg_replace('/[^a-zA-Z0-9-_]/', '_', $uniqueIdentifier);
        return lcfirst($uniqueIdentifier);
    }

    /**
     * Get the default value of the element
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        $formDefinition = $this->getRootForm();
        return $formDefinition->getElementDefaultValueByIdentifier($this->identifier);
    }

    /**
     * Set the default value of the element
     *
     * @param mixed $defaultValue
     * @return void
     */
    public function setDefaultValue($defaultValue)
    {
        $formDefinition = $this->getRootForm();
        $formDefinition->addElementDefaultValue($this->identifier, $defaultValue);
    }

    /**
     * Check if the element is required
     *
     * @return boolean
     */
    public function isRequired()
    {
        foreach ($this->getValidators() as $validator) {
            if ($validator instanceof NotEmptyValidator) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set a property of the element
     *
     * @param string $key
     * @param mixed $value
     */
    public function setProperty($key, $value)
    {
        $this->properties[$key] = $value;
    }

    /**
     * Get all properties
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Override this method in your custom FormElements if needed
     *
     * @param FormRuntime $formRuntime
     * @param mixed $elementValue
     * @return void
     */
    public function onSubmit(FormRuntime $formRuntime, &$elementValue)
    {
    }
}
