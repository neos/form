<?php
namespace Neos\Form\ViewHelpers;

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
use Neos\FluidAdaptor\ViewHelpers\FormViewHelper as FluidFormViewHelper;
use Neos\Form\Core\Runtime\FormRuntime;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Custom form ViewHelper that renders the form state instead of referrer fields
 */
class FormViewHelper extends FluidFormViewHelper
{
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
        /** @var FormRuntime $formRuntime */
        $formRuntime = $this->arguments['object'];
        $tagBuilder->addAttribute('value', $formRuntime->getSerializedFormState());
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
