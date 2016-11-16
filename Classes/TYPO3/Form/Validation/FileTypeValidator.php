<?php
namespace TYPO3\Form\Validation;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */


/**
 * The given $value is valid if it is an \TYPO3\Flow\Resource\Resource of the configured resolution
 * Note: a value of NULL or empty string ('') is considered valid
 */
class FileTypeValidator extends \TYPO3\Flow\Validation\Validator\AbstractValidator
{
    /**
     * @var array
     */
    protected $supportedOptions = array(
        'allowedExtensions' => array(array(), 'Array of allowed file extensions', 'array', true)
    );

    /**
     * The given $value is valid if it is an \TYPO3\Flow\Resource\Resource of the configured resolution
     * Note: a value of NULL or empty string ('') is considered valid
     *
     * @param \TYPO3\Flow\Resource\Resource $resource The resource object that should be validated
     * @return void
     * @api
     */
    protected function isValid($resource)
    {
        if (!$resource instanceof \TYPO3\Flow\Resource\Resource) {
            $this->addError('The given value was not a Resource instance.', 1327865587);
            return;
        }
        $fileExtension = $resource->getFileExtension();
        if ($fileExtension === null || $fileExtension === '') {
            $this->addError('The file has no file extension.', 1327865808);
            return;
        }
        if (!in_array($fileExtension, $this->options['allowedExtensions'])) {
            $this->addError('The file extension "%s" is not allowed.', 1327865764, array($resource->getFileExtension()));
            return;
        }
    }
}
