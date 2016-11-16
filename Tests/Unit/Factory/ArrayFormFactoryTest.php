<?php
namespace TYPO3\Form\Tests\Unit\Factory;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Form\Utility\SupertypeResolver;

/**
 * @covers \TYPO3\Form\Factory\ArrayFormFactory<extended>
 */
class ArrayFormFactoryTest extends \TYPO3\Flow\Tests\UnitTestCase
{
    /**
     * @test
     */
    public function simpleFormObjectIsReturned()
    {
        $factory = $this->getArrayFormFactory();

        $configuration = array(
            'identifier' => 'myFormIdentifier'
        );
        $form = $factory->build($configuration, 'default');
        $this->assertSame('myFormIdentifier', $form->getIdentifier());
    }

    /**
     * @test
     */
    public function formObjectWithSubRenderablesIsReturned()
    {
        $factory = $this->getArrayFormFactory();

        $configuration = array(
            'identifier' => 'myFormIdentifier',
            'renderables' => array(
                array(
                    'identifier' => 'page1',
                    'type' => 'TYPO3.Form:Page',
                    'renderables' => array(
                        array(
                            'identifier' => 'element1',
                            'type' => 'TYPO3.Form:TestElement',
                            'properties' => array(
                                'options' => array(
                                    0 => array(
                                        '_key' => 'MyKey',
                                        '_value' => 'MyValue'
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
        $form = $factory->build($configuration, 'default');
        $page1 = $form->getPageByIndex(0);
        $this->assertSame('page1', $page1->getIdentifier());
        $element1 = $form->getElementByIdentifier('element1');
        $this->assertSame('element1', $element1->getIdentifier());
        $this->assertSame(array('options' => array('MyKey' => 'MyValue')), $element1->getProperties());
    }

    /**
     * @test
     * @expectedException \TYPO3\Form\Exception\IdentifierNotValidException
     */
    public function renderableWithoutIdentifierThrowsException()
    {
        $factory = $this->getArrayFormFactory();

        $configuration = array(
            'identifier' => 'myFormIdentifier',
            'renderables' => array(
                array(
                    // identifier missing
                )
            )
        );
        $form = $factory->build($configuration, 'default');
    }

    /**
     * @return \TYPO3\Form\Factory\ArrayFormFactory
     */
    protected function getArrayFormFactory()
    {
        $settings = array(
            'presets' => array(
                'default' => array(
                    'formElementTypes' => array(
                        'TYPO3.Form:Form' => array(

                        ),
                        'TYPO3.Form:Page' => array(
                            'implementationClassName' => \TYPO3\Form\Core\Model\Page::class
                        ),
                        'TYPO3.Form:TestElement' => array(
                            'implementationClassName' => \TYPO3\Form\FormElements\GenericFormElement::class
                        )
                    )
                )
            )
        );

        $accessibleFactory = $this->buildAccessibleProxy(\TYPO3\Form\Factory\ArrayFormFactory::class);
        $factory = new $accessibleFactory;
        $factory->_set('formSettings', $settings);
        return $factory;
    }
}
