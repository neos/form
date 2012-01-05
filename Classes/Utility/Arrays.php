<?php
namespace TYPO3\Form\Utility;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * @todo document
 * @internal
 */
class Arrays {

	public static function assertAllArrayKeysAreValid($arrayToTest, $allowedArrayKeys) {
		$notAllowedArrayKeys = array_keys(array_diff_key($arrayToTest, array_flip($allowedArrayKeys)));
		if (count($notAllowedArrayKeys) !== 0) {
			throw new \TYPO3\Form\Exception\TypeDefinitionNotValidException(sprintf('The options "%s" were not allowed (allowed were: "%s")', implode(', ', $notAllowedArrayKeys), implode(', ', $allowedArrayKeys)), 1325697085);
		}
	}
}
?>