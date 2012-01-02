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

}

?>