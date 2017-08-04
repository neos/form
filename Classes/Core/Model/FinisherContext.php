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

use Neos\Form\Core\Runtime\FormRuntime;

/**
 * The context that is passed to each finisher when executed.
 * It acts like an EventObject that is able to stop propagation.
 *
 * **This class is not meant to be subclassed by developers.**
 */
class FinisherContext
{
    /**
     * If TRUE further finishers won't be invoked
     *
     * @var boolean
     * @internal
     */
    protected $cancelled = false;

    /**
     * A reference to the Form Runtime that the finisher belongs to
     *
     * @var \Neos\Form\Core\Runtime\FormRuntime
     * @internal
     */
    protected $formRuntime;

    /**
     * @param \Neos\Form\Core\Runtime\FormRuntime $formRuntime
     * @internal
     */
    public function __construct(FormRuntime $formRuntime)
    {
        $this->formRuntime = $formRuntime;
    }

    /**
     * Cancels the finisher invocation after the current finisher
     *
     * @return void
     * @api
     */
    public function cancel()
    {
        $this->cancelled = true;
    }

    /**
     * TRUE if no futher finishers should be invoked. Defaults to FALSE
     *
     * @return boolean
     * @internal
     */
    public function isCancelled()
    {
        return $this->cancelled;
    }

    /**
     * The Form Runtime that is associated with the current finisher
     *
     * @return \Neos\Form\Core\Runtime\FormRuntime
     * @api
     */
    public function getFormRuntime()
    {
        return $this->formRuntime;
    }

    /**
     * The values of the submitted form (after validation and property mapping)
     *
     * @return array
     * @api
     */
    public function getFormValues()
    {
        return $this->formRuntime->getFormState()->getFormValues();
    }
}
