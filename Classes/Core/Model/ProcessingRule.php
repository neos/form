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

use Neos\Error\Messages\Result;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Validation\Validator\ValidatorInterface;

/**
 * A processing Rule contains information for property mapping and validation.
 *
 * **This class is not meant to be subclassed by developers.**
 */
class ProcessingRule
{
    /**
     * The target data type the data should be converted to
     *
     * @var string
     */
    protected $dataType;

    /**
     * @var \Neos\Flow\Property\PropertyMappingConfiguration
     */
    protected $propertyMappingConfiguration;

    /**
     * @var \Neos\Flow\Validation\Validator\ConjunctionValidator
     */
    protected $validator;

    /**
     * @var Result
     */
    protected $processingMessages;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Property\PropertyMapper
     * @internal
     */
    protected $propertyMapper;

    /**
     * Constructs this processing rule
     */
    public function __construct()
    {
        $this->propertyMappingConfiguration = new \Neos\Flow\Property\PropertyMappingConfiguration();
        $this->validator = new \Neos\Flow\Validation\Validator\ConjunctionValidator();
        $this->processingMessages = new Result();
    }

    /**
     * @return \Neos\Flow\Property\PropertyMappingConfiguration
     */
    public function getPropertyMappingConfiguration()
    {
        return $this->propertyMappingConfiguration;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param string $dataType
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * Returns the child validators of the ConjunctionValidator that is bound to this processing rule
     *
     * @return \SplObjectStorage<\Neos\Flow\Validation\Validator\ValidatorInterface>
     * @internal
     */
    public function getValidators()
    {
        return $this->validator->getValidators();
    }

    /**
     * @param ValidatorInterface $validator
     * @return void
     */
    public function addValidator(ValidatorInterface $validator)
    {
        $this->validator->addValidator($validator);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function process($value)
    {
        if ($this->dataType !== null) {
            $value = $this->propertyMapper->convert($value, $this->dataType, $this->propertyMappingConfiguration);
            $messages = $this->propertyMapper->getMessages();
        } else {
            $messages = new Result();
        }

        $validationResult = $this->validator->validate($value);
        $messages->merge($validationResult);

        $this->processingMessages->merge($messages);
        return $value;
    }

    /**
     * @return Result
     */
    public function getProcessingMessages()
    {
        return $this->processingMessages;
    }
}
