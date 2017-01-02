<?php

namespace Neos\Form\Tests\Functional\Fixtures\FormFactories;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;

/**
 * Basic three-page form.
 */
class ThreePageFormWithValidationFactory extends \Neos\Form\Factory\AbstractFormFactory
{
    public function build(array $configuration, $presetName)
    {
        $formDefinition = new \Neos\Form\Core\Model\FormDefinition('three-page-form-with-validation', $this->getPresetConfiguration($presetName));

        $page1 = $formDefinition->createPage('page1');
        $page2 = $formDefinition->createPage('page2');
        $page3 = $formDefinition->createPage('page3');

        $page1->createElement('text1-1', 'Neos.Form:SingleLineText');
        $text21 = $page2->createElement('text2-1', 'Neos.Form:SingleLineText');
        $text21->addValidator(new \Neos\Flow\Validation\Validator\IntegerValidator());
        $page3->createElement('text3-1', 'Neos.Form:SingleLineText');

        return $formDefinition;
    }
}
