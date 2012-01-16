<?php
namespace TYPO3\Form\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 */
class ValidationRulesViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param \TYPO3\Form\Core\Model\FormElementInterface $element
	 * @return string validation rules
	 */
	public function render(\TYPO3\Form\Core\Model\FormElementInterface $element = NULL) {
		if ($element === NULL) {
			$element = $this->renderChildren();
		}
		if (!$element instanceof \TYPO3\Form\Core\Model\FormElementInterface) {
			throw new \TYPO3\Fluid\Core\ViewHelper\Exception('The given element does not implement FormElementInterface', 1326731778);
		}
		$rules = array();
		$rootValidator = $element->getValidator();

		// TODO What about DisjunctionValidator?
		if ($rootValidator instanceof \TYPO3\FLOW3\Validation\Validator\ConjunctionValidator) {
			foreach ($rootValidator->getValidators() as $validator) {
				$rules = array_merge($rules, $this->buildAttributeFromValidator($validator));
			}
		} else {
			$rules = $this->buildAttributeFromValidator($rootValidator);
		}
		return json_encode($rules);
	}

	/**
	 * @param \TYPO3\FLOW3\Validation\Validator\ValidatorInterface $validator
	 * @return array
	 */
	protected function buildAttributeFromValidator(\TYPO3\FLOW3\Validation\Validator\ValidatorInterface $validator) {
		$ruleName = str_replace('\\', '_', get_class($validator));
		if ($validator->getOptions() === array()) {
			$ruleOptions = TRUE;
		} else {
			$ruleOptions = $validator->getOptions();
		}
		return array($ruleName => $ruleOptions);
	}
}
?>