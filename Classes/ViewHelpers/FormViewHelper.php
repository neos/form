<?php
namespace TYPO3\Form\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * Custom form ViewHelper that renders custom referrer fields
 *
 * @todo document
 * @todo add this functionality to Fluid Form ViewHelper (ability to override/disable referrer fields)
 */
class FormViewHelper extends \TYPO3\Fluid\ViewHelpers\FormViewHelper {

	/**
	 * Renders hidden form fields for referrer information about
	 * the current request.
	 *
	 * @return string Hidden fields with referrer information
	 */
	protected function renderHiddenReferrerFields() {
		// TODO render form "meta data"
		return '';
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