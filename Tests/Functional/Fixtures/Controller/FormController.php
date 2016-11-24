<?php
namespace Neos\Form\Tests\Functional\Fixtures\Controller;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;

/**
 * Controller for rendering a form defined in
 */
class FormController extends \Neos\Flow\Mvc\Controller\ActionController
{
    /**
     * render the form identified by $formFactoryClassName
     *
     * @param string $formFactoryClassName
     */
    public function indexAction($formFactoryClassName)
    {
        $formFactoryClassName = 'Neos\Form\Tests\Functional\Fixtures\FormFactories\\' . $formFactoryClassName . 'Factory';
        /* @var $formFactory \Neos\Form\Factory\FormFactoryInterface */
        $formFactory = new $formFactoryClassName();
        $formDefinition = $formFactory->build(array(), 'default');

        $formRuntime = $formDefinition->bind($this->request, $this->response);

        return $formRuntime->render();
    }
}
