<?php
namespace TYPO3\Form\Tests\Unit\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Form\Utility\SupertypeResolver;

/**
 * Test for Supertype Resolver
 * @covers \TYPO3\Form\Utility\SupertypeResolver<extended>
 */
class SupertypeResolverTest extends \TYPO3\Flow\Tests\UnitTestCase {


	public function dataProviderForTypeResolving() {
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
				'superTypes' => array('typeFoo', 'typeBar'),
				'config2' => 'val2'
			),
			'typeWithSupertypes2' => array(
				'superTypes' => array('typeFoo', 'typeBar', 'typeBar2'),
				'config2' => 'val2'
			),
			'subTypeWithSupertypes2' => array(
				'superTypes' => array('typeWithSupertypes2'),
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
			)
		);
	}

	/**
	 * @dataProvider dataProviderForTypeResolving
	 * @test
	 */
	public function getMergedTypeDefinitionWorks($types, $typeName, $expected) {
		$supertypeResolver = new SupertypeResolver($types);
		$this->assertSame($expected, $supertypeResolver->getMergedTypeDefinition($typeName));
	}

	/**
	 * @test
	 * @expectedException TYPO3\Form\Exception\TypeDefinitionNotFoundException
	 */
	public function getMergedTypeDefinitionThrowsExceptionIfTypeNotFound() {
		$supertypeResolver = new SupertypeResolver(array());
		$supertypeResolver->getMergedTypeDefinition('nonExistingType');
	}
}
