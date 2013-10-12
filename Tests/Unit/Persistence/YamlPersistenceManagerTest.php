<?php
namespace TYPO3\Form\Tests\Unit\Persistence;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * @covers \TYPO3\Form\Persistence\YamlPersistenceManager<extended>
 */
class YamlPersistenceManagerTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Form\Persistence\YamlPersistenceManager
	 */
	protected $yamlPersistenceManager;

	public function setUp() {
		vfsStream::setup('someSavePath');
		$this->yamlPersistenceManager = new \TYPO3\Form\Persistence\YamlPersistenceManager();
		$this->yamlPersistenceManager->injectSettings(array(
				'yamlPersistenceManager' =>
					array('savePath' => vfsStream::url('someSavePath')
				)
			)
		);
	}

	/**
	 * @test
	 */
	public function injectSettingsCreatesSaveDirectoryIfItDoesntExist() {
		$this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('foo/bar'));
		$yamlPersistenceManager = new \TYPO3\Form\Persistence\YamlPersistenceManager();
		$settings = array(
			'yamlPersistenceManager' =>
				array('savePath' => vfsStream::url('someSavePath/foo/bar')
			)
		);
		$yamlPersistenceManager->injectSettings($settings);
		$this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('foo/bar'));
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Form\Exception\PersistenceManagerException
	 */
	public function loadThrowsExceptionIfSpecifiedFormDoesNotExist() {
		$yamlPersistenceManager = new \TYPO3\Form\Persistence\YamlPersistenceManager();
		$yamlPersistenceManager->load('someNonExistingPersistenceIdentifier');
	}

	/**
	 * @test
	 */
	public function loadReturnsFormDefinitionAsArray() {
		$mockYamlFormDefinition = 'type: \'TYPO3.Form:Form\'
identifier: formFixture
label: \'Form Fixture\'
';
		file_put_contents(vfsStream::url('someSavePath/mockFormPersistenceIdentifier.yaml'), $mockYamlFormDefinition);

		$actualResult = $this->yamlPersistenceManager->load('mockFormPersistenceIdentifier');
		$expectedResult = array(
			'type' => 'TYPO3.Form:Form',
			'identifier' => 'formFixture',
			'label' => 'Form Fixture'
		);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function saveStoresFormDefinitionAsYaml() {
		$mockArrayFormDefinition = array(
			'type' => 'TYPO3.Form:Form',
			'identifier' => 'formFixture',
			'label' => 'Form Fixture'
		);
		$this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('mockFormPersistenceIdentifier.yaml'));

		$this->yamlPersistenceManager->save('mockFormPersistenceIdentifier', $mockArrayFormDefinition);
		$expectedResult = 'type: \'TYPO3.Form:Form\'
identifier: formFixture
label: \'Form Fixture\'
';
		$actualResult = file_get_contents(vfsStream::url('someSavePath/mockFormPersistenceIdentifier.yaml'));
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function existsReturnsFalseIfTheSpecifiedFormDoesNotExist() {
		$this->assertFalse($this->yamlPersistenceManager->exists('someNonExistingPersistenceIdentifier'));
	}

	/**
	 * @test
	 */
	public function existsReturnsTrueIfTheSpecifiedFormExists() {
		$mockYamlFormDefinition = 'type: \'TYPO3.Form:Form\'
identifier: formFixture
label: \'Form Fixture\'
';
		file_put_contents(vfsStream::url('someSavePath/mockFormPersistenceIdentifier.yaml'), $mockYamlFormDefinition);
		$this->assertTrue($this->yamlPersistenceManager->exists('mockFormPersistenceIdentifier'));
	}

	/**
	 * @test
	 */
	public function listFormsReturnsAnEmptyArrayIfNoFormsAreAvailable() {
		$this->assertEquals(array(), $this->yamlPersistenceManager->listForms());
	}

	/**
	 * @test
	 */
	public function listFormsReturnsAvailableForms() {
		$mockYamlFormDefinition1 = 'type: \'TYPO3.Form:Form\'
identifier: formFixture1
label: \'Form Fixture1\'
';
		$mockYamlFormDefinition2 = 'type: \'TYPO3.Form:Form\'
identifier: formFixture2
label: \'Form Fixture2\'
';
		file_put_contents(vfsStream::url('someSavePath/mockForm1.yaml'), $mockYamlFormDefinition1);
		file_put_contents(vfsStream::url('someSavePath/mockForm2.yaml'), $mockYamlFormDefinition2);
		file_put_contents(vfsStream::url('someSavePath/noForm.txt'), 'this should be skipped');

		$expectedResult = array(
			array(
				'identifier' => 'formFixture1',
				'name' => 'Form Fixture1',
				'persistenceIdentifier' => 'mockForm1',
			),
			array(
				'identifier' => 'formFixture2',
				'name' => 'Form Fixture2',
				'persistenceIdentifier' => 'mockForm2',
			),
		);
		$this->assertEquals($expectedResult, $this->yamlPersistenceManager->listForms());
	}

}
