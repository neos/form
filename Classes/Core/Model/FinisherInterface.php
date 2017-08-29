<?php
namespace Neos\Form\Core\Model;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * Finisher that can be attached to a form in order to be invoked
 * as soon as the complete form is submitted
 */
interface FinisherInterface
{
    /**
     * Executes the finisher
     *
     * @param \Neos\Form\Core\Model\FinisherContext $finisherContext The Finisher context that contains the current Form Runtime and Response
     * @return void
     * @api
     */
    public function execute(FinisherContext $finisherContext);

    /**
     * @param array $options configuration options in the format array('option1' => 'value1', 'option2' => 'value2', ...)
     * @return void
     * @api
     */
    public function setOptions(array $options);

    /**
     * Sets a single finisher option (@see setOptions())
     *
     * @param string $optionName name of the option to be set
     * @param mixed $optionValue value of the option
     * @return void
     * @api
     */
    public function setOption($optionName, $optionValue);
}
