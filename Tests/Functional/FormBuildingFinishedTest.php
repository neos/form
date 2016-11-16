<?php
namespace TYPO3\Form\Tests\Functional;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */
use Symfony\Component\DomCrawler\Field\InputFormField;

/**
 * Testcase for onBuildingFinished
 *
 * @group large
 */
class FormBuildingFinishedTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function aFormElementCanAddNewSubelementsWithValidationApplied()
    {
        $this->browser->request('http://localhost/test/form/simpleform/TestingFormBuildingFinished');

        $form = $this->browser->getForm();
        $form['--testing']['myInteger']->setValue('no int');

        $this->gotoNextFormPage($form);
        $this->assertSame('no int', $form['--testing']['myInteger']->getValue());
        $this->assertSame(' error', $this->browser->getCrawler()->filterXPath('//*[contains(@class,"error")]//input[@id="testing-myInteger"]')->attr('class'));

        $this->browser->getForm();
    }
}
