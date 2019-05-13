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

use Neos\Form\Factory\ArrayFormFactory;

/**
 * Simple form for testing
 */
class TestingFormBuildingFinishedFactory extends ArrayFormFactory
{
    public function build(array $configuration, $presetName)
    {
        $configuration = [
            'type' => 'Neos.Form:Form',
            'identifier' => 'testing',
            'label' => 'My Label',
            'renderables' => [
                [
                    'type' => 'Neos.Form:Page',
                    'identifier' => 'general',
                    'renderables' => [
                        [
                            'type' => 'Neos.Form:TestingFormElementWithSubElements',
                            'identifier' => 'subel',
                        ]
                    ]
                ]
            ]
        ];
        return parent::build($configuration, $presetName);
    }
}
