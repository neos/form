<?php
namespace TYPO3\Form\Exception;

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
 * This Exception is thrown in the FormRuntime if the PropertyMapper throws
 * a \TYPO3\Flow\Property\Exception. It adds some more Information to
 * better understand why the PropertyMapper failed to map the properties
 *
 * @api
 */
class PropertyMappingException extends \TYPO3\Form\Exception {
}
