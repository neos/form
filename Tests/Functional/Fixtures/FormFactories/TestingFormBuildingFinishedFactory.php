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
 * Simple form for testing
 */
class TestingFormBuildingFinishedFactory extends \TYPO3\Form\Factory\ArrayFormFactory
{
    public function build(array $configuration, $presetName)
    {
        $configuration = array(
            'type' => 'TYPO3.Form:Form',
            'identifier' => 'testing',
            'label' => 'My Label',
            'renderables' => array(
                array(
                    'type' => 'TYPO3.Form:Page',
                    'identifier' => 'general',
                    'renderables' => array(
                        array(
                            'type' => 'TYPO3.Form:TestingFormElementWithSubElements',
                            'identifier' => 'subel',
                        )
                    )
                )
            )
        );
        return parent::build($configuration, $presetName);
    }
}
