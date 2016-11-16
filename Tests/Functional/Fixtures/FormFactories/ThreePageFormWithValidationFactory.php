<?php
namespace TYPO3\Form\Tests\Functional\Fixtures\FormFactories;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;

/**
 * Basic three-page form
 */
class ThreePageFormWithValidationFactory extends \TYPO3\Form\Factory\AbstractFormFactory
{
    public function build(array $configuration, $presetName)
    {
        $formDefinition = new \TYPO3\Form\Core\Model\FormDefinition('three-page-form-with-validation', $this->getPresetConfiguration($presetName));

        $page1 = $formDefinition->createPage('page1');
        $page2 = $formDefinition->createPage('page2');
        $page3 = $formDefinition->createPage('page3');

        $page1->createElement('text1-1', 'TYPO3.Form:SingleLineText');
        $text21 = $page2->createElement('text2-1', 'TYPO3.Form:SingleLineText');
        $text21->addValidator(new \TYPO3\Flow\Validation\Validator\IntegerValidator());
        $page3->createElement('text3-1', 'TYPO3.Form:SingleLineText');

        return $formDefinition;
    }
}
