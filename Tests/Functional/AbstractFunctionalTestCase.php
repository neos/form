<?php

namespace Neos\Form\Tests\Functional;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */
use Symfony\Component\DomCrawler\Field\InputFormField;

/**
 * Testcase for Simple Form.
 *
 * @group large
 */
abstract class AbstractFunctionalTestCase extends \Neos\Flow\Tests\FunctionalTestCase
{
    /**
     * @var \Neos\Flow\Http\Client\Browser
     */
    protected $browser;

    /**
     * Initializer.
     */
    public function setUp()
    {
        parent::setUp();

        $route = new \Neos\Flow\Mvc\Routing\Route();
        $route->setUriPattern('test/form/simpleform/{formFactoryClassName}');
        $route->setDefaults([
            '@package'    => 'Neos.Form',
            '@subpackage' => 'Tests\Functional\Fixtures',
            '@controller' => 'Form',
            '@action'     => 'index',
            '@format'     => 'html',
        ]);
        $route->setAppendExceedingArguments(true);
        $this->router->addRoute($route);
    }

    /**
     * Go to the next form page.
     *
     * @param \Symfony\Component\DomCrawler\Form $form
     *
     * @return \Neos\Flow\Http\Response
     */
    protected function gotoNextFormPage(\Symfony\Component\DomCrawler\Form $form)
    {
        $nextButton = $this->browser->getCrawler()->filterXPath('//nav[@class="form-navigation"]/*/*[contains(@class, "next")]/button');
        $nextButton->rewind();
        $form->set(new InputFormField($nextButton->current()));

        return $this->browser->submit($form);
    }

    /**
     * Go to the previous form page.
     *
     * @param \Symfony\Component\DomCrawler\Form $form
     *
     * @return \Neos\Flow\Http\Response
     */
    protected function gotoPreviousFormPage(\Symfony\Component\DomCrawler\Form $form)
    {
        $previousButton = $this->browser->getCrawler()->filterXPath('//nav[@class="form-navigation"]/*/*[contains(@class, "previous")]/button');
        $previousButton->rewind();
        $form->set(new InputFormField($previousButton->current()));

        return $this->browser->submit($form);
    }
}
