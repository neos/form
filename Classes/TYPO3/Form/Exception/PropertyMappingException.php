<?php
namespace TYPO3\Form\Exception;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * This Exception is thrown in the FormRuntime if the PropertyMapper throws
 * a \TYPO3\Flow\Property\Exception. It adds some more Information to
 * better understand why the PropertyMapper failed to map the properties
 *
 * @api
 */
class PropertyMappingException extends \TYPO3\Form\Exception
{
}
