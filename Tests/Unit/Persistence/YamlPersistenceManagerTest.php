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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Neos\Flow\Tests\UnitTestCase;
use Neos\Form\Exception\PersistenceManagerException;
use Neos\Form\Persistence\YamlPersistenceManager;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\Assert;

#[CoversClass(YamlPersistenceManager::class)]
class YamlPersistenceManagerTest extends UnitTestCase
{
    /**
     * @var YamlPersistenceManager
     */
    protected $yamlPersistenceManager;

    public function setUp(): void
    {
        vfsStream::setup('someSavePath');
        $this->yamlPersistenceManager = new YamlPersistenceManager();
        $this->yamlPersistenceManager->injectSettings(
            [
                'yamlPersistenceManager' =>
                    ['savePath' => vfsStream::url('someSavePath')
                    ]
            ]
        );
    }

    #[Test]
    public function injectSettingsCreatesSaveDirectoryIfItDoesntExist()
    {
        Assert::assertFalse(vfsStreamWrapper::getRoot()->hasChild('foo/bar'));
        $yamlPersistenceManager = new YamlPersistenceManager();
        $settings = [
            'yamlPersistenceManager' =>
                ['savePath' => vfsStream::url('someSavePath/foo/bar')
                ]
        ];
        $yamlPersistenceManager->injectSettings($settings);
        Assert::assertTrue(vfsStreamWrapper::getRoot()->hasChild('foo/bar'));
    }


    #[Test]
    public function loadThrowsExceptionIfSavePathIsNotSet()
    {
        $this->expectException(PersistenceManagerException::class);
        $yamlPersistenceManager = new YamlPersistenceManager();
        $yamlPersistenceManager->load('dummy');
    }

    #[Test]
    public function loadThrowsExceptionIfSpecifiedFormDoesNotExist()
    {
        $this->expectException(PersistenceManagerException::class);

        $yamlPersistenceManager = new YamlPersistenceManager();
        $settings = [
            'yamlPersistenceManager' =>
                ['savePath' => vfsStream::url('someSavePath/foo/bar')
                ]
        ];
        $yamlPersistenceManager->injectSettings($settings);
        $yamlPersistenceManager->load('someNonExistingPersistenceIdentifier');
    }

    #[Test]
    public function loadReturnsFormDefinitionAsArray()
    {
        $mockYamlFormDefinition = 'type: \'Neos.Form:Form\'
identifier: formFixture
label: \'Form Fixture\'
';
        file_put_contents(vfsStream::url('someSavePath/mockFormPersistenceIdentifier.yaml'), $mockYamlFormDefinition);

        $actualResult = $this->yamlPersistenceManager->load('mockFormPersistenceIdentifier');
        $expectedResult = [
            'type' => 'Neos.Form:Form',
            'identifier' => 'formFixture',
            'label' => 'Form Fixture'
        ];
        Assert::assertEquals($expectedResult, $actualResult);
    }

    #[Test]
    public function saveStoresFormDefinitionAsYaml()
    {
        $mockArrayFormDefinition = [
            'type' => 'Neos.Form:Form',
            'identifier' => 'formFixture',
            'label' => 'Form Fixture'
        ];
        Assert::assertFalse(vfsStreamWrapper::getRoot()->hasChild('mockFormPersistenceIdentifier.yaml'));

        $this->yamlPersistenceManager->save('mockFormPersistenceIdentifier', $mockArrayFormDefinition);
        $expectedResult = 'type: \'Neos.Form:Form\'
identifier: formFixture
label: \'Form Fixture\'
';
        $actualResult = file_get_contents(vfsStream::url('someSavePath/mockFormPersistenceIdentifier.yaml'));
        Assert::assertEquals($expectedResult, $actualResult);
    }

    #[Test]
    public function existsReturnsFalseIfTheSpecifiedFormDoesNotExist()
    {
        $this->assertFalse($this->yamlPersistenceManager->exists('someNonExistingPersistenceIdentifier'));
    }

    #[Test]
    public function existsReturnsTrueIfTheSpecifiedFormExists()
    {
        $mockYamlFormDefinition = 'type: \'Neos.Form:Form\'
identifier: formFixture
label: \'Form Fixture\'
';
        file_put_contents(vfsStream::url('someSavePath/mockFormPersistenceIdentifier.yaml'), $mockYamlFormDefinition);
        Assert::assertTrue($this->yamlPersistenceManager->exists('mockFormPersistenceIdentifier'));
    }

    #[Test]
    public function listFormsThrowsExceptionIfSavePathIsNotSet()
    {
        $this->expectException(PersistenceManagerException::class);
        $yamlPersistenceManager = new YamlPersistenceManager();
        $yamlPersistenceManager->listForms();
    }


    #[Test]
    public function listFormsReturnsAnEmptyArrayIfNoFormsAreAvailable()
    {
        Assert::assertEquals([], $this->yamlPersistenceManager->listForms());
    }

    #[Test]
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

        $expectedResult = [
            [
                'identifier' => 'formFixture1',
                'name' => 'Form Fixture1',
                'persistenceIdentifier' => 'mockForm1',
            ],
            [
                'identifier' => 'formFixture2',
                'name' => 'Form Fixture2',
                'persistenceIdentifier' => 'mockForm2',
            ],
        ];
        Assert::assertEquals($expectedResult, $this->yamlPersistenceManager->listForms());
    }
}
