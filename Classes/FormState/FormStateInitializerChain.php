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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Core\Runtime\FormState;
use Neos\Utility\PositionalArraySorter;

final class FormStateInitializerChain
{

    /**
     * @Flow\InjectConfiguration(path="formStateInitializerChain")
     * @var string[][]
     */
    protected $formStateInitializerChain;

    public function initializeFormState(FormDefinition $formDefinition, ActionRequest $actionRequest): FormState
    {
        $formState = new FormState();
        $sortedChain = (new PositionalArraySorter($this->formStateInitializerChain))->toArray();

        foreach ($sortedChain as $initializer) {
            $class = $initializer['class'] ?? '';
            if (!is_a($class, FormStateInitializerInterface::class, true)) {
                throw new \RuntimeException(sprintf('The given class "%s" does not implement the interface %s', $class, FormStateInitializerInterface::class), 1648204540);
            }

            $formState = (new $class())->initializeState($formDefinition, $actionRequest, $formState);
        }

        return $formState;
    }
}
