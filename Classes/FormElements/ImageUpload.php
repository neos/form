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

use Neos\Flow\Property\PropertyMappingConfiguration;
use Neos\Flow\Property\TypeConverter\PersistentObjectConverter;
use Neos\Form\Core\Model\AbstractFormElement;
use Neos\Form\Core\Runtime\FormRuntime;
use Neos\Media\Domain\Model\Image;
use Neos\Media\TypeConverter\AssetInterfaceConverter;
use Neos\Media\Validator\ImageTypeValidator;

/**
 * An image upload form element
 */
class ImageUpload extends AbstractFormElement
{
    /**
     * @return void
     */
    public function initializeFormElement()
    {
        /** @var PropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = $this->getRootForm()->getProcessingRule($this->getIdentifier())->getPropertyMappingConfiguration();

        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
        $propertyMappingConfiguration->setTypeConverterOption(AssetInterfaceConverter::class, AssetInterfaceConverter::CONFIGURATION_ONE_PER_RESOURCE, true);
        $propertyMappingConfiguration->allowProperties('resource');

        $this->setDataType(Image::class);
    }

    /**
     * Add ImageTypeValidator just before submitting so that the "allowedTypes" can be changed at runtime
     *
     * @param FormRuntime $formRuntime
     * @param mixed $elementValue
     * @return void
     */
    public function onSubmit(FormRuntime $formRuntime, &$elementValue)
    {
        $imageTypeValidator = new ImageTypeValidator(array('allowedTypes' => $this->properties['allowedTypes']));
        $this->addValidator($imageTypeValidator);
    }
}
