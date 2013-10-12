<?php
namespace TYPO3\Form\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Collection of static array utility functions
 * @internal
 */
class Arrays {

	/**
	 * Validates the given $arrayToTest by checking if an element is not in $allowedArrayKeys.
	 *
	 * @param array $arrayToTest
	 * @param array $allowedArrayKeys
	 * @return void
	 * @throws \TYPO3\Form\Exception\TypeDefinitionNotValidException if an element in $arrayToTest is not in $allowedArrayKeys
	 */
	public static function assertAllArrayKeysAreValid(array $arrayToTest, array $allowedArrayKeys) {
		$notAllowedArrayKeys = array_keys(array_diff_key($arrayToTest, array_flip($allowedArrayKeys)));
		if (count($notAllowedArrayKeys) !== 0) {
			throw new \TYPO3\Form\Exception\TypeDefinitionNotValidException(sprintf('The options "%s" were not allowed (allowed were: "%s")', implode(', ', $notAllowedArrayKeys), implode(', ', $allowedArrayKeys)), 1325697085);
		}
	}
}
