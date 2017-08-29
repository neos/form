<?php
namespace Neos\Form\Exception;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Form\Exception as FormException;

/**
 * This exception is thrown if the form definition would get an inconsistent state, like:
 *
 * - adding a page to two different forms
 *
 *
 * @api
 */
class FormDefinitionConsistencyException extends FormException
{
}
