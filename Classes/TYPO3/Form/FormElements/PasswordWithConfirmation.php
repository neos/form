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

use Neos\Flow\Annotations as Flow;

/**
 * A password with confirmation form element
 */
class PasswordWithConfirmation extends \TYPO3\Form\Core\Model\AbstractFormElement
{
    public function onSubmit(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime, &$elementValue)
    {
        if ($elementValue['password'] !== $elementValue['confirmation']) {
            $processingRule = $this->getRootForm()->getProcessingRule($this->getIdentifier());
            $processingRule->getProcessingMessages()->addError(new \Neos\Flow\Error\Error('Password doesn\'t match confirmation', 1334768052));
        }
        $elementValue = $elementValue['password'];
    }
}
