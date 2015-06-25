<?php
namespace TYPO3\Form\Tests\Unit\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Uri;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Controller\ControllerContext;
use TYPO3\Flow\Tests\UnitTestCase;
use TYPO3\Form\ViewHelpers\FormViewHelper;

/**
 * Tests for the custom FormViewHelper
*/
class FormViewHelperTest extends UnitTestCase {

	/**
	 * @var FormViewHelper
	 */
	protected $formViewHelper;

	/**
	 * @var ControllerContext|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockControllerContext;

	public function setUp() {
		$this->formViewHelper = $this->getAccessibleMock(FormViewHelper::class, ['hasArgument']);

		$this->mockControllerContext = $this->getMockBuilder(ControllerContext::class)->disableOriginalConstructor()->getMock();

		$this->inject($this->formViewHelper, 'controllerContext', $this->mockControllerContext);
	}

	/**
	 * @return array
	 */
	public function getFormActionUriDataProvider() {
		return [
			['requestUri' => '', 'sectionArgument' => NULL, 'expectedResult' => ''],
			['requestUri' => '#section', 'sectionArgument' => NULL, 'expectedResult' => '#section'],
			['requestUri' => '/foo', 'sectionArgument' => NULL, 'expectedResult' => '/foo'],
			['requestUri' => '/foo#section', 'sectionArgument' => NULL, 'expectedResult' => '/foo#section'],
			['requestUri' => 'http://absolute/uri', 'sectionArgument' => NULL, 'expectedResult' => 'http://absolute/uri'],
			['requestUri' => 'http://absolute/uri#section', 'sectionArgument' => NULL, 'expectedResult' => 'http://absolute/uri#section'],

			['requestUri' => '', 'sectionArgument' => 'newSection', 'expectedResult' => '#newSection'],
			['requestUri' => '#section', 'sectionArgument' => 'newSection', 'expectedResult' => '#newSection'],
			['requestUri' => '/foo', 'sectionArgument' => 'newSection', 'expectedResult' => '/foo#newSection'],
			['requestUri' => '/foo#section', 'sectionArgument' => 'newSection', 'expectedResult' => '/foo#newSection'],
			['requestUri' => 'http://absolute/uri', 'sectionArgument' => 'newSection', 'expectedResult' => 'http://absolute/uri#newSection'],
			['requestUri' => 'http://absolute/uri#section', 'sectionArgument' => 'newSection', 'expectedResult' => 'http://absolute/uri#newSection'],
		];
	}

	/**
	 * @test
	 * @param string $requestUri
	 * @param string $sectionArgument
	 * @param string $expectedResult
	 * @dataProvider getFormActionUriDataProvider
	 */
	public function getFormActionUriTests($requestUri, $sectionArgument, $expectedResult) {
		$mockActionRequest = $this->getMockBuilder(ActionRequest::class)->disableOriginalConstructor()->getMock();
		$this->mockControllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockActionRequest));

		$mockHttpRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
		$mockActionRequest->expects($this->any())->method('getHttpRequest')->will($this->returnValue($mockHttpRequest));

		$mockUri = $this->getMockBuilder(Uri::class)->disableOriginalConstructor()->getMock();
		$mockUri->expects($this->any())->method('__toString')->will($this->returnValue($requestUri));
		$mockHttpRequest->expects($this->any())->method('getUri')->will($this->returnValue($mockUri));

		$this->formViewHelper->expects($this->any())->method('hasArgument')->with('section')->will($this->returnValue($sectionArgument !== NULL));
		$this->formViewHelper->_set('arguments', ['section' => $sectionArgument]);

		$this->assertSame($expectedResult, $this->formViewHelper->_call('getFormActionUri'));
	}


}