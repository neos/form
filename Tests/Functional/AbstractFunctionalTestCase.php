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
 * Testcase for Simple Form
 *
 * @group large
 */
abstract class AbstractFunctionalTestCase extends \TYPO3\Flow\Tests\FunctionalTestCase
{
    /**
     * @var \TYPO3\Flow\Http\Client\Browser
     */
    protected $browser;

    /**
     * Initializer
     */
    public function setUp()
    {
        parent::setUp();

        $route = new \TYPO3\Flow\Mvc\Routing\Route();
        $route->setUriPattern('test/form/simpleform/{formFactoryClassName}');
        $route->setDefaults(array(
            '@package' => 'TYPO3.Form',
            '@subpackage' => 'Tests\Functional\Fixtures',
            '@controller' => 'Form',
            '@action' => 'index',
            '@format' => 'html'
        ));
        $route->setAppendExceedingArguments(true);
        $this->router->addRoute($route);
    }

    /**
     * Go to the next form page
     *
     * @param \Symfony\Component\DomCrawler\Form $form
     * @return \TYPO3\Flow\Http\Response
     */
    protected function gotoNextFormPage(\Symfony\Component\DomCrawler\Form $form)
    {
        $nextButton = $this->browser->getCrawler()->filterXPath('//nav[@class="form-navigation"]/*/*[contains(@class, "next")]/button');
        $nextButton->rewind();
        $form->set(new InputFormField($nextButton->current()));

        return $this->browser->submit($form);
    }

    /**
     * Go to the previous form page
     *
     * @param \Symfony\Component\DomCrawler\Form $form
     * @return \TYPO3\Flow\Http\Response
     */
    protected function gotoPreviousFormPage(\Symfony\Component\DomCrawler\Form $form)
    {
        $previousButton = $this->browser->getCrawler()->filterXPath('//nav[@class="form-navigation"]/*/*[contains(@class, "previous")]/button');
        $previousButton->rewind();
        $form->set(new InputFormField($previousButton->current()));

        return $this->browser->submit($form);
    }
}
