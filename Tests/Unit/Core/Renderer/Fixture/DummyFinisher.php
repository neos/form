<?php
namespace TYPO3\Form\Tests\Unit\Core\Runtime\Renderer\Fixture;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Form\Core\Model\FinisherContext;

/**
* Dummy finisher for testing
*/
class DummyFinisher implements \TYPO3\Form\Core\Model\FinisherInterface
{
    public $cb = null;

    /**
     * Executes the finisher
     *
     * @param \TYPO3\Form\Core\Model\FinisherContext $finisherContext The Finisher context that contains the current Form Runtime and Response
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
