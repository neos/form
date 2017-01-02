<?php

namespace Neos\Form\Tests\Unit\Factory;

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
 * @covers \Neos\Form\Factory\ArrayFormFactory<extended>
 */
class ArrayFormFactoryTest extends \Neos\Flow\Tests\UnitTestCase
{
    /**
     * @test
     */
    public function simpleFormObjectIsReturned()
    {
        $factory = $this->getArrayFormFactory();

        $configuration = [
            'identifier' => 'myFormIdentifier',
        ];
        $form = $factory->build($configuration, 'default');
        $this->assertSame('myFormIdentifier', $form->getIdentifier());
    }

    /**
     * @test
     */
    public function formObjectWithSubRenderablesIsReturned()
    {
        $factory = $this->getArrayFormFactory();

        $configuration = [
            'identifier'  => 'myFormIdentifier',
            'renderables' => [
                [
                    'identifier'  => 'page1',
                    'type'        => 'Neos.Form:Page',
                    'renderables' => [
                        [
                            'identifier' => 'element1',
                            'type'       => 'Neos.Form:TestElement',
                            'properties' => [
                                'options' => [
                                    0 => [
                                        '_key'   => 'MyKey',
                                        '_value' => 'MyValue',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $form = $factory->build($configuration, 'default');
        $page1 = $form->getPageByIndex(0);
        $this->assertSame('page1', $page1->getIdentifier());
        $element1 = $form->getElementByIdentifier('element1');
        $this->assertSame('element1', $element1->getIdentifier());
        $this->assertSame(['options' => ['MyKey' => 'MyValue']], $element1->getProperties());
    }

    /**
     * @test
     * @expectedException \Neos\Form\Exception\IdentifierNotValidException
     */
    public function renderableWithoutIdentifierThrowsException()
    {
        $factory = $this->getArrayFormFactory();

        $configuration = [
            'identifier'  => 'myFormIdentifier',
            'renderables' => [
                [
                    // identifier missing
                ],
            ],
        ];
        $form = $factory->build($configuration, 'default');
    }

    /**
     * @return \Neos\Form\Factory\ArrayFormFactory
     */
    protected function getArrayFormFactory()
    {
        $settings = [
            'presets' => [
                'default' => [
                    'formElementTypes' => [
                        'Neos.Form:Form' => [

                        ],
                        'Neos.Form:Page' => [
                            'implementationClassName' => \Neos\Form\Core\Model\Page::class,
                        ],
                        'Neos.Form:TestElement' => [
                            'implementationClassName' => \Neos\Form\FormElements\GenericFormElement::class,
                        ],
                    ],
                ],
            ],
        ];

        $accessibleFactory = $this->buildAccessibleProxy(\Neos\Form\Factory\ArrayFormFactory::class);
        $factory = new $accessibleFactory();
        $factory->_set('formSettings', $settings);

        return $factory;
    }
}
