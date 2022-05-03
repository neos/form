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
use Neos\Flow\Security\Cryptography\HashService;
use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Core\Runtime\FormState;

class DefaultFormStateInitializer implements FormStateInitializerInterface
{
    /**
     * @Flow\Inject
     * @var HashService
     */
    protected $hashService;

    public function initializeFormState(FormDefinition $formDefinition, ActionRequest $actionRequest): FormState
    {
        $serializedFormStateWithHmac = $actionRequest->getInternalArgument('__state');
        if ($serializedFormStateWithHmac !== null) {
            $serializedFormState = $this->hashService->validateAndStripHmac($serializedFormStateWithHmac);
            /** @noinspection UnserializeExploitsInspection The unserialize call is safe because of the HMAC check above */
            return unserialize(base64_decode($serializedFormState));
        }

        return new FormState();
    }
}
