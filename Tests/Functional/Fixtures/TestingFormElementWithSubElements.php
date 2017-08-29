<?php
namespace Neos\Form\Tests\Functional\Fixtures;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Validation\Validator\IntegerValidator;
use Neos\Form\FormElements\Section;

/**
 * Form element that amends itself with another field having a validator
 */
class TestingFormElementWithSubElements extends Section
{
    /**
     * This is a callback that is invoked by the Form Factory after the whole form has been built.
     *
     * @return void
     * @api
     */
    public function onBuildingFinished()
    {
        $element = $this->createElement('myInteger', 'Neos.Form:SingleLineText');
        $element->addValidator(new IntegerValidator());
    }
}
