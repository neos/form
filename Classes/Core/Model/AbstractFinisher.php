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

use Neos\Utility\Arrays;
use Neos\Utility\ObjectAccess;

/**
 * Finisher base class.
 *
 * **This class is meant to be subclassed by developers**
 */
abstract class AbstractFinisher implements FinisherInterface
{
    /**
     * The options which have been set from the outside. Instead of directly
     * accessing them, you should rather use parseOption().
     *
     * @var array
     * @internal
     */
    protected $options = [];

    /**
     * These are the default options of the finisher.
     * Override them in your concrete implementation.
     * Default options should not be changed from "outside"
     *
     * @var array
     * @api
     */
    protected $defaultOptions = [];

    /**
     * @var \Neos\Form\Core\Model\FinisherContext
     * @api
     */
    protected $finisherContext;

    /**
     * @param array $options configuration options in the format array('option1' => 'value1', 'option2' => 'value2', ...)
     * @return void
     * @api
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
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
        $this->options[$optionName] = $optionValue;
    }

    /**
     * Executes the finisher
     *
     * @param \Neos\Form\Core\Model\FinisherContext $finisherContext The Finisher context that contains the current Form Runtime and Response
     * @return void
     * @api
     */
    final public function execute(FinisherContext $finisherContext)
    {
        $this->finisherContext = $finisherContext;
        $this->executeInternal();
    }

    /**
     * This method is called in the concrete finisher whenever self::execute() is called.
     *
     * Override and fill with your own implementation!
     *
     * @return void
     * @api
     */
    abstract protected function executeInternal();

    /**
     * Read the option called $optionName from $this->options, and parse {...}
     * as object accessors.
     *
     * if $optionName was not found, the corresponding default option is returned (from $this->defaultOptions)
     *
     * @param string $optionName
     * @return mixed
     * @api
     */
    protected function parseOption($optionName)
    {
        if (!isset($this->options[$optionName]) || $this->options[$optionName] === '') {
            if (!is_null(Arrays::getValueByPath($this->options, $optionName)) && Arrays::getValueByPath($this->options, $optionName) !== '') {
                $option = Arrays::getValueByPath($this->options, $optionName);
            } elseif (!is_null(Arrays::getValueByPath($this->defaultOptions, $optionName)) && Arrays::getValueByPath($this->defaultOptions, $optionName) !== '') {
                $option = Arrays::getValueByPath($this->defaultOptions, $optionName);
            } elseif (isset($this->defaultOptions[$optionName])) {
                $option = $this->defaultOptions[$optionName];
            } else {
                return null;
            }
        } else {
            $option = $this->options[$optionName];
        }
        if (!is_string($option)) {
            return $option;
        }
        $formRuntime = $this->finisherContext->getFormRuntime();
        return preg_replace_callback('/{([^}]+)}/', function ($match) use ($formRuntime) {
            return ObjectAccess::getPropertyPath($formRuntime, $match[1]);
        }, $option);
    }
}
