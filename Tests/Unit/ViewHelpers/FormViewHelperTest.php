<?php
namespace Neos\Form\Tests\Unit\ViewHelpers;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Tests\UnitTestCase;
use Neos\Form\ViewHelpers\FormViewHelper;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Tests for the custom FormViewHelper
*/
class FormViewHelperTest extends UnitTestCase
{
    /**
     * @var FormViewHelper
     */
    protected $formViewHelper;

    /**
     * @var ControllerContext|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $mockControllerContext;

    public function setUp(): void
    {
        $this->formViewHelper = $this->getAccessibleMock(FormViewHelper::class, ['hasArgument']);

        $this->mockControllerContext = $this->getMockBuilder(ControllerContext::class)->disableOriginalConstructor()->getMock();

        $this->inject($this->formViewHelper, 'controllerContext', $this->mockControllerContext);
    }

    /**
     * @return array
     */
    public function getFormActionUriDataProvider()
    {
        return [
            ['requestUri' => '', 'sectionArgument' => null, 'expectedResult' => ''],
            ['requestUri' => '#section', 'sectionArgument' => null, 'expectedResult' => '#section'],
            ['requestUri' => '/foo', 'sectionArgument' => null, 'expectedResult' => '/foo'],
            ['requestUri' => '/foo#section', 'sectionArgument' => null, 'expectedResult' => '/foo#section'],
            ['requestUri' => 'http://absolute/uri', 'sectionArgument' => null, 'expectedResult' => 'http://absolute/uri'],
            ['requestUri' => 'http://absolute/uri#section', 'sectionArgument' => null, 'expectedResult' => 'http://absolute/uri#section'],

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
     * @throws \ReflectionException
     */
    public function getFormActionUriTests($requestUri, $sectionArgument, $expectedResult)
    {
        $mockActionRequest = $this->getMockBuilder(ActionRequest::class)->disableOriginalConstructor()->getMock();
        $this->mockControllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockActionRequest));

        $mockHttpRequest = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $mockActionRequest->expects($this->any())->method('getHttpRequest')->will($this->returnValue($mockHttpRequest));

        $mockUri = $this->getMockBuilder(Uri::class)->disableOriginalConstructor()->getMock();
        $mockUri->expects($this->any())->method('__toString')->will($this->returnValue($requestUri));
        $mockHttpRequest->expects($this->any())->method('getUri')->will($this->returnValue($mockUri));

        $this->formViewHelper->expects($this->any())->method('hasArgument')->with('section')->will($this->returnValue($sectionArgument !== null));
        $this->formViewHelper->_set('arguments', ['section' => $sectionArgument]);

        Assert::assertSame($expectedResult, $this->formViewHelper->_call('getFormActionUri'));
    }
}
