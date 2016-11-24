<?php
namespace TYPO3\Form\ViewHelpers;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\FluidAdaptor\ViewHelpers\FormViewHelper as FluidFormViewHelper;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Custom form ViewHelper that renders the form state instead of referrer fields
 */
class FormViewHelper extends FluidFormViewHelper
{
    /**
     * @Flow\Inject
     * @var \Neos\Flow\Security\Cryptography\HashService
     */
    protected $hashService;

    /**
     * Renders hidden form fields for referrer information about
     * the current request.
     *
     * @return string Hidden fields with referrer information
     */
    protected function renderHiddenReferrerFields()
    {
        $tagBuilder = new TagBuilder('input');
        $tagBuilder->addAttribute('type', 'hidden');
        $tagBuilder->addAttribute('name', $this->prefixFieldName('__state'));
        $serializedFormState = base64_encode(serialize($this->arguments['object']->getFormState()));
        $tagBuilder->addAttribute('value', $this->hashService->appendHmac($serializedFormState));
        return $tagBuilder->render();
    }

    /**
     * We do NOT return NULL as in this case, the Form ViewHelpers do not enter $objectAccessorMode.
     * However, we return the *empty string* to avoid double-prefixing the current form,
     * as the prefixing is handled by the subrequest which is bound to the form.
     *
     * @return string
     */
    protected function getFormObjectName()
    {
        return '';
    }

    /**
     * Overrides the forms action URI to be the same as the currently requested URI
     *
     * @return string
     */
    protected function getFormActionUri()
    {
        /** @var ActionRequest $actionRequest */
        $actionRequest = $this->controllerContext->getRequest();
        $uri = $actionRequest->getHttpRequest()->getUri();
        if ($this->hasArgument('section')) {
            $uri = preg_replace('/#.*$/', '', $uri) . '#' . $this->arguments['section'];
        }
        return (string)$uri;
    }
}
