<?php
namespace TYPO3\Form\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Custom form ViewHelper that renders the form state instead of referrer fields
 */
class FormViewHelper extends \TYPO3\Fluid\ViewHelpers\FormViewHelper {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\Cryptography\HashService
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
}

?>