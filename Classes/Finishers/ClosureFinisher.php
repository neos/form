<?php
namespace Neos\Form\Finishers;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Form\Core\Model\AbstractFinisher;
use Neos\Form\Exception\FinisherException;

/**
 * A simple finisher that invokes a closure when executed
 *
 * Usage:
 * //...
 * $closureFinisher = new \Neos\Form\Finishers\ClosureFinisher();
 * $closureFinisher->setOption('closure', function($finisherContext) {
 *   $formRuntime = $finisherContext->getFormRuntime();
 *   // ...
 * });
 * $formDefinition->addFinisher($closureFinisher);
 * // ...
 */
class ClosureFinisher extends AbstractFinisher
{
    /**
     * @var array
     */
    protected $defaultOptions = array(
        'closure' => null
    );

    /**
     * Executes this finisher
     * @see AbstractFinisher::execute()
     *
     * @return void
     * @throws FinisherException
     */
    protected function executeInternal()
    {
        /** @var $closure \Closure */
        $closure = $this->parseOption('closure');
        if ($closure === null) {
            return;
        }
        if (!$closure instanceof \Closure) {
            throw new FinisherException(sprintf('The option "closure" must be of type Closure, "%s" given.', gettype($closure)), 1332155239);
        }
        $closure($this->finisherContext);
    }
}
