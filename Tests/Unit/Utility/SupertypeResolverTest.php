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

use Neos\Flow\Tests\UnitTestCase;
use Neos\Form\Exception\TypeDefinitionNotFoundException;
use Neos\Form\Utility\SupertypeResolver;
use PHPUnit\Framework\Assert;

/**
 * Test for Supertype Resolver
 * @covers \Neos\Form\Utility\SupertypeResolver<extended>
 */
class SupertypeResolverTest extends UnitTestCase
{
    public function dataProviderForTypeResolving()
    {
        $types = [
            'typeFoo' => [
                'config1' => 'val1'
            ],
            'typeBar' => [
                'config3' => 'val3'
            ],
            'typeBar2' => [
                'config3' => 'val3a'
            ],
            'typeWithSupertypes' => [
                'superTypes' => ['typeFoo' => true, 'typeBar' => true],
                'config2' => 'val2'
            ],
            'typeWithSupertypes2' => [
                'superTypes' => ['typeFoo' => true, 'typeBar' => true, 'typeBar2' => true],
                'config2' => 'val2'
            ],
            'subTypeWithSupertypes2' => [
                'superTypes' => ['typeWithSupertypes2' => true],
                'config2' => 'val2a'
            ],
            'typeWithSupertypesInArraySyntax' => [
                'superTypes' => ['typeFoo', 'typeBar'],
                'config2' => 'val2'
            ],
            'typeWithSupertypes2InArraySyntax' => [
                'superTypes' => ['typeFoo', 'typeBar', 'typeBar2'],
                'config2' => 'val2'
            ],
            'subTypeWithSupertypes2InArraySyntax' => [
                'superTypes' => ['typeWithSupertypes2InArraySyntax'],
                'config2' => 'val2a'
            ],
        ];
        return [
            'without supertype' => [
                'types' => $types,
                'typeName' => 'typeFoo',
                'expected' => [
                    'config1' => 'val1'
                ]
            ],
            'with a list of supertypes a' => [
                'types' => $types,
                'typeName' => 'typeWithSupertypes',
                'expected' => [
                    'config1' => 'val1',
                    'config3' => 'val3',
                    'config2' => 'val2'
                ]
            ],
            'with a list of supertypes b' => [
                'types' => $types,
                'typeName' => 'typeWithSupertypes2',
                'expected' => [
                    'config1' => 'val1',
                    'config3' => 'val3a',
                    'config2' => 'val2'
                ]
            ],
            'with recursive supertypes' => [
                'types' => $types,
                'typeName' => 'subTypeWithSupertypes2',
                'expected' => [
                    'config1' => 'val1',
                    'config3' => 'val3a',
                    'config2' => 'val2a'
                ]
            ],
            'with a list of supertypes' => [
                'types' => $types,
                'typeName' => 'typeWithSupertypesInArraySyntax',
                'expected' => [
                    'config1' => 'val1',
                    'config3' => 'val3',
                    'config2' => 'val2'
                ]
            ],
            'with a list of supertypes c' => [
                'types' => $types,
                'typeName' => 'typeWithSupertypes2InArraySyntax',
                'expected' => [
                    'config1' => 'val1',
                    'config3' => 'val3a',
                    'config2' => 'val2'
                ]
            ],
            'with recursive supertypes d' => [
                'types' => $types,
                'typeName' => 'subTypeWithSupertypes2InArraySyntax',
                'expected' => [
                    'config1' => 'val1',
                    'config3' => 'val3a',
                    'config2' => 'val2a'
                ]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTypeResolving
     * @test
     */
    public function getMergedTypeDefinitionWorks($types, $typeName, $expected)
    {
        $supertypeResolver = new SupertypeResolver($types);
        Assert::assertSame($expected, $supertypeResolver->getMergedTypeDefinition($typeName));
    }

    /**
     * @test
     */
    public function getMergedTypeDefinitionThrowsExceptionIfTypeNotFound()
    {
        $this->expectException(TypeDefinitionNotFoundException::class);

        $supertypeResolver = new SupertypeResolver([]);
        $supertypeResolver->getMergedTypeDefinition('nonExistingType');
    }
}
