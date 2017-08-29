<?php
namespace Neos\Form\Tests\Unit\Persistence;

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
use Neos\Form\Persistence\YamlPersistenceManager;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * @covers \Neos\Form\Persistence\YamlPersistenceManager<extended>
 */
class YamlPersistenceManagerTest extends UnitTestCase
{
    /**
     * @var YamlPersistenceManager
     */
    protected $yamlPersistenceManager;

    public function setUp()
    {
        vfsStream::setup('someSavePath');
        $this->yamlPersistenceManager = new YamlPersistenceManager();
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
    public function injectSettingsCreatesSaveDirectoryIfItDoesntExist()
    {
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('foo/bar'));
        $yamlPersistenceManager = new YamlPersistenceManager();
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
     * @expectedException \Neos\Form\Exception\PersistenceManagerException
     */
    public function loadThrowsExceptionIfSavePathIsNotSet()
    {
        $yamlPersistenceManager = new YamlPersistenceManager();
        $yamlPersistenceManager->load('dummy');
    }

    /**
     * @test
     * @expectedException \Neos\Form\Exception\PersistenceManagerException
     */
    public function loadThrowsExceptionIfSpecifiedFormDoesNotExist()
    {
        $yamlPersistenceManager = new YamlPersistenceManager();
        $settings = array(
            'yamlPersistenceManager' =>
                array('savePath' => vfsStream::url('someSavePath/foo/bar')
                )
        );
        $yamlPersistenceManager->injectSettings($settings);
        $yamlPersistenceManager->load('someNonExistingPersistenceIdentifier');
    }

    /**
     * @test
     */
    public function loadReturnsFormDefinitionAsArray()
    {
        $mockYamlFormDefinition = 'type: \'Neos.Form:Form\'
identifier: formFixture
label: \'Form Fixture\'
';
        file_put_contents(vfsStream::url('someSavePath/mockFormPersistenceIdentifier.yaml'), $mockYamlFormDefinition);

        $actualResult = $this->yamlPersistenceManager->load('mockFormPersistenceIdentifier');
        $expectedResult = array(
            'type' => 'Neos.Form:Form',
            'identifier' => 'formFixture',
            'label' => 'Form Fixture'
        );
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function saveStoresFormDefinitionAsYaml()
    {
        $mockArrayFormDefinition = array(
            'type' => 'Neos.Form:Form',
            'identifier' => 'formFixture',
            'label' => 'Form Fixture'
        );
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('mockFormPersistenceIdentifier.yaml'));

        $this->yamlPersistenceManager->save('mockFormPersistenceIdentifier', $mockArrayFormDefinition);
        $expectedResult = 'type: \'Neos.Form:Form\'
identifier: formFixture
label: \'Form Fixture\'
';
        $actualResult = file_get_contents(vfsStream::url('someSavePath/mockFormPersistenceIdentifier.yaml'));
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function existsReturnsFalseIfTheSpecifiedFormDoesNotExist()
    {
        $this->assertFalse($this->yamlPersistenceManager->exists('someNonExistingPersistenceIdentifier'));
    }

    /**
     * @test
     */
    public function existsReturnsTrueIfTheSpecifiedFormExists()
    {
        $mockYamlFormDefinition = 'type: \'Neos.Form:Form\'
identifier: formFixture
label: \'Form Fixture\'
';
        file_put_contents(vfsStream::url('someSavePath/mockFormPersistenceIdentifier.yaml'), $mockYamlFormDefinition);
        $this->assertTrue($this->yamlPersistenceManager->exists('mockFormPersistenceIdentifier'));
    }

    /**
     * @test
     * @expectedException \Neos\Form\Exception\PersistenceManagerException
     */
    public function listFormsThrowsExceptionIfSavePathIsNotSet()
    {
        $yamlPersistenceManager = new YamlPersistenceManager();
        $yamlPersistenceManager->listForms();
    }


    /**
     * @test
     */
    public function listFormsReturnsAnEmptyArrayIfNoFormsAreAvailable()
    {
        $this->assertEquals(array(), $this->yamlPersistenceManager->listForms());
    }

    /**
     * @test
     */
    public function listFormsReturnsAvailableForms()
    {
        $mockYamlFormDefinition1 = 'type: \'Neos.Form:Form\'
identifier: formFixture1
label: \'Form Fixture1\'
';
        $mockYamlFormDefinition2 = 'type: \'Neos.Form:Form\'
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
