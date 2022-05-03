<?php
declare(strict_types=1);

namespace Neos\Form\FormState;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Mvc\ActionRequest;
use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Core\Runtime\FormState;

interface FormStateInitializerInterface
{
    public function initializeFormState(FormDefinition $formDefinition, ActionRequest $actionRequest): FormState;
}
