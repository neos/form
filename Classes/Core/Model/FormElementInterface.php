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

use Neos\Flow\Validation\Validator\ValidatorInterface;
use Neos\Form\Core\Runtime\FormRuntime;

/**
 * A base form element interface, which can be the starting point for creating
 * custom (PHP-based) Form Elements.
 *
 * A *FormElement* is a part of a *Page*, which in turn is part of a FormDefinition.
 * See {@link FormDefinition} for an in-depth explanation.
 *
 * **Often, you should rather subclass {@link AbstractFormElement} instead of
 * implementing this interface.**
 */
interface FormElementInterface extends Renderable\RenderableInterface
{
    /**
     * Will be called as soon as the element is (tried to be) added to a form
     * @see registerInFormIfPossible()
     *
     * @return void
     * @internal
     */
    public function initializeFormElement();

    /**
     * Returns a unique identifier of this element.
     * While element identifiers are only unique within one form,
     * this includes the identifier of the form itself, making it "globally" unique
     *
     * @return string the "globally" unique identifier of this element
     * @api
     */
    public function getUniqueIdentifier();

    /**
     * Get the default value with which the Form Element should be initialized
     * during display.
     *
     * @return mixed the default value for this Form Element
     * @api
     */
    public function getDefaultValue();

    /**
     * Set the default value with which the Form Element should be initialized
     * during display.
     *
     * @param mixed $defaultValue the default value for this Form Element
     * @api
     */
    public function setDefaultValue($defaultValue);

    /**
     * Set an element-specific configuration property.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     * @api
     */
    public function setProperty($key, $value);

    /**
     * Get all element-specific configuration properties
     *
     * @return array
     * @api
     */
    public function getProperties();

    /**
     * Set a rendering option
     *
     * @param string $key
     * @param mixed $value
     * @api
     */
    public function setRenderingOption($key, $value);

    /**
     * Returns the child validators of the ConjunctionValidator that is registered for this element
     *
     * @return \SplObjectStorage<\Neos\Flow\Validation\Validator\ValidatorInterface>
     * @internal
     */
    public function getValidators();

    /**
     * Registers a validator for this element
     *
     * @param ValidatorInterface $validator
     * @return void
     * @api
     */
    public function addValidator(ValidatorInterface $validator);

    /**
     * Set the target data type for this element
     *
     * @param string $dataType the target data type
     * @return void
     * @api
     */
    public function setDataType($dataType);

    /**
     * Whether or not this element is required
     *
     * @return boolean
     * @api
     */
    public function isRequired();

    /**
     * This callback is invoked by the FormRuntime whenever values are mapped and validated
     * (after a form page was submitted)
     *
     * @param FormRuntime $formRuntime
     * @param mixed $elementValue submitted value of the element *before post processing*
     * @return void
     * @see \Neos\Form\Core\Runtime\FormRuntime::mapAndValidate()
     * @api
     */
    public function onSubmit(FormRuntime $formRuntime, &$elementValue);
}
