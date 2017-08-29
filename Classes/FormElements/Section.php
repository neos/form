<?php
namespace Neos\Form\FormElements;

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
use Neos\Flow\Validation\Validator\ValidatorInterface;
use Neos\Form\Core\Model\AbstractSection;
use Neos\Form\Core\Model\FormElementInterface;

/**
 * A Section, being part of a bigger Page
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * This class contains multiple FormElements ({@link FormElementInterface}).
 *
 * Please see {@link FormDefinition} for an in-depth explanation.
 *
 * Once we support traits, the duplicated code between AbstractFormElement and Section could be extracted to a Trait.
 */
class Section extends AbstractSection implements FormElementInterface
{
    /**
     * @var array
     */
    protected $properties = [];

    /**
     * Will be called as soon as the element is (tried to be) added to a form
     * @see registerInFormIfPossible()
     *
     * @return void
     * @internal
     */
    public function initializeFormElement()
    {
    }

    /**
     * Returns a unique identifier of this element.
     * While element identifiers are only unique within one form,
     * this includes the identifier of the form itself, making it "globally" unique
     *
     * @return string the "globally" unique identifier of this element
     * @api
     */
    public function getUniqueIdentifier()
    {
        $formDefinition = $this->getRootForm();
        return sprintf('%s-%s', $formDefinition->getIdentifier(), $this->identifier);
    }

    /**
     * Get the default value with which the Form Element should be initialized
     * during display.
     * Note: This is currently not used for section elements
     *
     * @return mixed the default value for this Form Element
     * @api
     */
    public function getDefaultValue()
    {
        return null;
    }

    /**
     * Set the default value with which the Form Element should be initialized
     * during display.
     * Note: This is currently ignored for section elements
     *
     * @param mixed $defaultValue the default value for this Form Element
     * @api
     */
    public function setDefaultValue($defaultValue)
    {
    }


    /**
     * Get all element-specific configuration properties
     *
     * @return array
     * @api
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Set an element-specific configuration property.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     * @api
     */
    public function setProperty($key, $value)
    {
        $this->properties[$key] = $value;
    }

    /**
     * Set the rendering option $key to $value.
     *
     * @param string $key
     * @param mixed $value
     * @api
     * @return void
     */
    public function setRenderingOption($key, $value)
    {
        $this->renderingOptions[$key] = $value;
    }

    /**
     * Get all validators on the element
     *
     * @return \SplObjectStorage
     */
    public function getValidators()
    {
        $formDefinition = $this->getRootForm();
        return $formDefinition->getProcessingRule($this->getIdentifier())->getValidators();
    }

    /**
     * Add a validator to the element
     *
     * @param ValidatorInterface $validator
     * @return void
     */
    public function addValidator(ValidatorInterface $validator)
    {
        $formDefinition = $this->getRootForm();
        $formDefinition->getProcessingRule($this->getIdentifier())->addValidator($validator);
    }

    /**
     * Whether or not this element is required
     *
     * @return boolean
     * @api
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
}
