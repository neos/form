<?php
namespace Neos\Form\Tests\Unit\Core\Runtime\Renderer\Fixture;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Form\Core\Model\FinisherContext;
use Neos\Form\Core\Model\FinisherInterface;

/**
* Dummy finisher for testing
*/
class DummyFinisher implements FinisherInterface
{
    /**
     * @var callable
     */
    public $cb = null;

    /**
     * Executes the finisher
     *
     * @param \Neos\Form\Core\Model\FinisherContext $finisherContext The Finisher context that contains the current Form Runtime and Response
     * @return void
     * @api
     */
    public function execute(FinisherContext $finisherContext)
    {
        $cb = $this->cb;
        $cb($finisherContext);
    }

    /**
     * @param array $options configuration options in the format array('@action' => 'foo', '@controller' => 'bar', '@package' => 'baz')
     * @return void
     * @api
     */
    public function setOptions(array $options)
    {
    }

    /**
     * Sets a single finisher option (@see setOptions())
     *
     * @param string $optionName name of the option to be set
     * @param mixed $optionValue value of the option
     * @return void
     * @api
     */
    public function setOption($optionName, $optionValue)
    {
    }
}
