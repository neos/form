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

use Neos\Flow\Annotations as Flow;

/**
 * A generic file upload form element
 */
class FileUpload extends \Neos\Form\Core\Model\AbstractFormElement
{
    /**
     * @return void
     */
    public function initializeFormElement()
    {
        $this->setDataType(\Neos\Flow\ResourceManagement\PersistentResource::class);
    }

    /**
     * Add FileTypeValidator just before submitting so that the "allowedExtension" can be changed at runtime
     *
     * @param \Neos\Form\Core\Runtime\FormRuntime $formRuntime
     * @param mixed $elementValue
     * @return void
     */
    public function onSubmit(\Neos\Form\Core\Runtime\FormRuntime $formRuntime, &$elementValue)
    {
        $fileTypeValidator = new \Neos\Form\Validation\FileTypeValidator(array('allowedExtensions' => $this->properties['allowedExtensions']));
        $this->addValidator($fileTypeValidator);
    }
}
