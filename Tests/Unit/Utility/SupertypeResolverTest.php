<?php
namespace Neos\Form\Tests\Unit\Utility;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Form\Utility\SupertypeResolver;

/**
 * Test for Supertype Resolver
 * @covers \Neos\Form\Utility\SupertypeResolver<extended>
 */
class SupertypeResolverTest extends \Neos\Flow\Tests\UnitTestCase
{
    public function dataProviderForTypeResolving()
    {
        $types = array(
            'typeFoo' => array(
                'config1' => 'val1'
            ),
            'typeBar' => array(
                'config3' => 'val3'
            ),
            'typeBar2' => array(
                'config3' => 'val3a'
            ),
            'typeWithSupertypes' => array(
                'superTypes' => array('typeFoo' => true, 'typeBar' => true),
                'config2' => 'val2'
            ),
            'typeWithSupertypes2' => array(
                'superTypes' => array('typeFoo' => true, 'typeBar' => true, 'typeBar2' => true),
                'config2' => 'val2'
            ),
            'subTypeWithSupertypes2' => array(
                'superTypes' => array('typeWithSupertypes2' => true),
                'config2' => 'val2a'
            ),
            'typeWithSupertypesInArraySyntax' => array(
                'superTypes' => array('typeFoo', 'typeBar'),
                'config2' => 'val2'
            ),
            'typeWithSupertypes2InArraySyntax' => array(
                'superTypes' => array('typeFoo', 'typeBar', 'typeBar2'),
                'config2' => 'val2'
            ),
            'subTypeWithSupertypes2InArraySyntax' => array(
                'superTypes' => array('typeWithSupertypes2InArraySyntax'),
                'config2' => 'val2a'
            ),
        );
        return array(
            'without supertype' => array(
                'types' => $types,
                'typeName' => 'typeFoo',
                'expected' => array(
                    'config1' => 'val1'
                )
            ),
            'with a list of supertypes' => array(
                'types' => $types,
                'typeName' => 'typeWithSupertypes',
                'expected' => array(
                    'config1' => 'val1',
                    'config3' => 'val3',
                    'config2' => 'val2'
                )
            ),
            'with a list of supertypes' => array(
                'types' => $types,
                'typeName' => 'typeWithSupertypes2',
                'expected' => array(
                    'config1' => 'val1',
                    'config3' => 'val3a',
                    'config2' => 'val2'
                )
            ),
            'with recursive supertypes' => array(
                'types' => $types,
                'typeName' => 'subTypeWithSupertypes2',
                'expected' => array(
                    'config1' => 'val1',
                    'config3' => 'val3a',
                    'config2' => 'val2a'
                )
            ),
            'with a list of supertypes' => array(
                'types' => $types,
                'typeName' => 'typeWithSupertypesInArraySyntax',
                'expected' => array(
                    'config1' => 'val1',
                    'config3' => 'val3',
                    'config2' => 'val2'
                )
            ),
            'with a list of supertypes' => array(
                'types' => $types,
                'typeName' => 'typeWithSupertypes2InArraySyntax',
                'expected' => array(
                    'config1' => 'val1',
                    'config3' => 'val3a',
                    'config2' => 'val2'
                )
            ),
            'with recursive supertypes' => array(
                'types' => $types,
                'typeName' => 'subTypeWithSupertypes2InArraySyntax',
                'expected' => array(
                    'config1' => 'val1',
                    'config3' => 'val3a',
                    'config2' => 'val2a'
                )
            )
        );
    }

    /**
     * @dataProvider dataProviderForTypeResolving
     * @test
     */
    public function getMergedTypeDefinitionWorks($types, $typeName, $expected)
    {
        $supertypeResolver = new SupertypeResolver($types);
        $this->assertSame($expected, $supertypeResolver->getMergedTypeDefinition($typeName));
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\TypeDefinitionNotFoundException
     */
    public function getMergedTypeDefinitionThrowsExceptionIfTypeNotFound()
    {
        $supertypeResolver = new SupertypeResolver(array());
        $supertypeResolver->getMergedTypeDefinition('nonExistingType');
    }
}
