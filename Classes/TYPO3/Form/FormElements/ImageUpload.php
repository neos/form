<?php
namespace TYPO3\Form\FormElements;

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
use TYPO3\Flow\Property\PropertyMappingConfiguration;
use TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\Media\TypeConverter\AssetInterfaceConverter;

/**
 * An image upload form element
 */
class ImageUpload extends \TYPO3\Form\Core\Model\AbstractFormElement
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

        $this->setDataType(\TYPO3\Media\Domain\Model\Image::class);
        $imageTypeValidator = new \TYPO3\Media\Validator\ImageTypeValidator(array('allowedTypes' => $this->properties['allowedTypes']));
        $this->addValidator($imageTypeValidator);
    }
}
