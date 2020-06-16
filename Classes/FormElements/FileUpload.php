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
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceTypeConverter;
use Neos\Flow\Validation\Exception\InvalidValidationOptionsException;
use Neos\Form\Core\Model\AbstractFormElement;
use Neos\Form\Core\Runtime\FormRuntime;
use Neos\Form\Exception\FormDefinitionConsistencyException;
use Neos\Form\Validation\FileTypeValidator;

/**
 * A generic file upload form element
 */
class FileUpload extends AbstractFormElement
{
    /**
     * @return void
     */
    public function initializeFormElement()
    {
        $this->setDataType(PersistentResource::class);
    }

    /**
     * Add FileTypeValidator just before submitting so that the "allowedExtension" can be changed at runtime
     *
     * @param FormRuntime $formRuntime
     * @param mixed $elementValue
     * @return void
     * @throws InvalidValidationOptionsException | FormDefinitionConsistencyException
     */
    public function onSubmit(FormRuntime $formRuntime, &$elementValue)
    {
        if (isset($this->properties['resourceCollection'])) {
            /** @var PropertyMappingConfiguration $propertyMappingConfiguration */
            $propertyMappingConfiguration = $this->getRootForm()->getProcessingRule($this->getIdentifier())->getPropertyMappingConfiguration();
            $propertyMappingConfiguration->setTypeConverterOption(ResourceTypeConverter::class, ResourceTypeConverter::CONFIGURATION_COLLECTION_NAME, $this->properties['resourceCollection']);
        }
        $fileTypeValidator = new FileTypeValidator(array('allowedExtensions' => $this->properties['allowedExtensions']));
        $this->addValidator($fileTypeValidator);
    }
}
