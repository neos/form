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

use Neos\Error\Messages\Error;
use Neos\Form\Core\Model\AbstractFormElement;
use Neos\Form\Core\Runtime\FormRuntime;

/**
 * A password with confirmation form element
 */
class PasswordWithConfirmation extends AbstractFormElement
{
    public function onSubmit(FormRuntime $formRuntime, &$elementValue)
    {
        if ($elementValue['password'] !== $elementValue['confirmation']) {
            $processingRule = $this->getRootForm()->getProcessingRule($this->getIdentifier());
            $processingRule->getProcessingMessages()->addError(new Error('Password doesn\'t match confirmation', 1334768052));
        }
        $elementValue = $elementValue['password'];
    }
}
