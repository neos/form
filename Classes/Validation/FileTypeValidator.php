<?php
namespace Neos\Form\Validation;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\Validation\Validator\AbstractValidator;

/**
 * The given $value is valid if it is an \Neos\Flow\ResourceManagement\PersistentResource of the configured resolution
 * Note: a value of NULL or empty string ('') is considered valid
 */
class FileTypeValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $supportedOptions = array(
        'allowedExtensions' => array([], 'Array of allowed file extensions', 'array', true)
    );

    /**
     * The given $value is valid if it is an \Neos\Flow\ResourceManagement\PersistentResource of the configured resolution
     * Note: a value of NULL or empty string ('') is considered valid
     *
     * @param PersistentResource $resource The resource object that should be validated
     * @return void
     * @api
     */
    protected function isValid($resource)
    {
        if (!$resource instanceof PersistentResource) {
            $this->addError('The given value was not a PersistentResource instance.', 1327865587);
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
