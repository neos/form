<?php

namespace Neos\Form\Utility;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * Collection of static array utility functions.
 *
 * @internal
 */
class Arrays
{
    /**
     * Validates the given $arrayToTest by checking if an element is not in $allowedArrayKeys.
     *
     * @param array $arrayToTest
     * @param array $allowedArrayKeys
     *
     * @throws \Neos\Form\Exception\TypeDefinitionNotValidException if an element in $arrayToTest is not in $allowedArrayKeys
     *
     * @return void
     */
    public static function assertAllArrayKeysAreValid(array $arrayToTest, array $allowedArrayKeys)
    {
        $notAllowedArrayKeys = array_keys(array_diff_key($arrayToTest, array_flip($allowedArrayKeys)));
        if (count($notAllowedArrayKeys) !== 0) {
            throw new \Neos\Form\Exception\TypeDefinitionNotValidException(sprintf('The options "%s" were not allowed (allowed were: "%s")', implode(', ', $notAllowedArrayKeys), implode(', ', $allowedArrayKeys)), 1325697085);
        }
    }
}
