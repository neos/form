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
use Neos\Flow\Property\TypeConverter\DateTimeConverter;
use Neos\Form\Core\Model\AbstractFormElement;
use Neos\Form\Core\Runtime\FormRuntime;

/**
 * A date picker form element
 */
class DatePicker extends AbstractFormElement
{
    /**
     * @return void
     */
    public function initializeFormElement()
    {
        $this->setDataType('DateTime');
    }

    public function onSubmit(FormRuntime $formRuntime, &$elementValue)
    {
        if (!isset($this->properties['dateFormat'])) {
            return;
        }
        /** @var PropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = $this->getRootForm()->getProcessingRule($this->getIdentifier())->getPropertyMappingConfiguration();

        $propertyMappingConfiguration->setTypeConverterOption(DateTimeConverter::class, DateTimeConverter::CONFIGURATION_DATE_FORMAT, $this->properties['dateFormat']);
    }
}
