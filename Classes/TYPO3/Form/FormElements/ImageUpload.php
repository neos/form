<?php
namespace TYPO3\Form\FormElements;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

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
