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

use Neos\Form\Core\Renderer\UnknownFormElementRenderer;
use Neos\Form\Core\Runtime\FormRuntime;
use Neos\Form\Exception\IdentifierNotValidException;

/**
 * A Form Element that has no Definition in the current preset.
 *
 * @api
 */
class UnknownFormElement extends Renderable\AbstractRenderable implements FormElementInterface
{
    /**
     * The identifier of this Element
     *
     * @var string
     */
    protected $identifier;

    /**
     * Abstract "type" of this Element (which couldn't be resolved)
     *
     * @var string
     */
    protected $type;

    /**
     * Constructor. Needs this FormElement's identifier and the FormElement type
     *
     * @param string $identifier The FormElement's identifier
     * @param string $type The Form Element Type
     * @throws IdentifierNotValidException
     */
    public function __construct($identifier, $type)
    {
        if (!is_string($identifier) || strlen($identifier) === 0) {
            throw new IdentifierNotValidException('The given identifier was not a string or the string was empty.', 1382364370);
        }
        $this->identifier = $identifier;
        $this->type = $type;
    }

    /**
     * Returns a unique identifier of this element.
     * While element identifiers are only unique within one form,
     * this includes the identifier of the form itself, making it "globally" unique
     *
     * @return string the "globally" unique identifier of this element
     */
    public function getUniqueIdentifier()
    {
        $formDefinition = $this->getRootForm();
        $uniqueIdentifier = sprintf('%s-%s', $formDefinition->getIdentifier(), $this->identifier);
        $uniqueIdentifier = preg_replace('/[^a-zA-Z0-9-_]/', '_', $uniqueIdentifier);
        return lcfirst($uniqueIdentifier);
    }

    /**
     * Unknown Form Elements are rendered with the UnknownFormElementRenderer
     *
     * @return string the renderer class name
     */
    public function getRendererClassName()
    {
        return UnknownFormElementRenderer::class;
    }

    /**
     * Not used in this implementation
     *
     * @return void
     */
    public function initializeFormElement()
    {
    }

    /**
     * @return mixed the default value for this Form Element
     */
    public function getDefaultValue()
    {
        return null;
    }

    /**
     * Not used in this implementation
     *
     * @param mixed $defaultValue the default value for this Form Element
     */
    public function setDefaultValue($defaultValue)
    {
    }

    /**
     * Not used in this implementation
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setProperty($key, $value)
    {
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return [];
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return false;
    }

    /**
     * Not used in this implementation
     *
     * @param FormRuntime $formRuntime
     * @param mixed $elementValue submitted value of the element *before post processing*
     * @return void
     * @see \Neos\Form\Core\Runtime\FormRuntime::mapAndValidate()
     */
    public function onSubmit(FormRuntime $formRuntime, &$elementValue)
    {
    }
}
