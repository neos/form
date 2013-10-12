<?php
namespace TYPO3\Form\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Custom form ViewHelper that renders the form state instead of referrer fields
 */
class FormViewHelper extends \TYPO3\Fluid\ViewHelpers\FormViewHelper {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * Renders hidden form fields for referrer information about
	 * the current request.
	 *
	 * @return string Hidden fields with referrer information
	 */
	protected function renderHiddenReferrerFields() {
		$tagBuilder = new \TYPO3\Fluid\Core\ViewHelper\TagBuilder('input');
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
	protected function getFormObjectName() {
		return '';
	}

	/**
	 * Overrides the forms action URI to be the same as the currently requested URI
	 *
	 * @return string
	 */
	protected function getFormActionUri() {
		$httpRequest = $this->controllerContext->getRequest()->getHttpRequest();
		return $httpRequest->getUri();
	}
}
