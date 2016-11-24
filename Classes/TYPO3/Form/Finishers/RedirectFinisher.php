<?php
namespace TYPO3\Form\Finishers;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Routing\UriBuilder;
use TYPO3\Form\Core\Model\AbstractFinisher;

/**
 * This finisher redirects to another Controller or a specific URI.
 */
class RedirectFinisher extends AbstractFinisher
{
    /**
     * @var array
     */
    protected $defaultOptions = [
        'package' => null,
        'controller' => null,
        'action' => '',
        'arguments' => [],
        'uri' => '',
        'delay' => 0,
        'statusCode' => 303
    ];

    /**
     * Executes this finisher
     * @see AbstractFinisher::execute()
     *
     * @return void
     * @throws \TYPO3\Form\Exception\FinisherException
     */
    public function executeInternal()
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $request = $formRuntime->getRequest()->getMainRequest();

        $delay = (integer)$this->parseOption('delay');
        $statusCode = $this->parseOption('statusCode');
        $uri = trim($this->parseOption('uri'));

        
        if ($uri === '') {
            $uri = $this->buildActionUri($request);
        }

        $uriParts = parse_url($uri);
        if (!isset($uriParts['scheme']) || $uriParts['scheme'] === '') {
            $uri = $request->getHttpRequest()->getBaseUri() . $uri;
        }

        $escapedUri = htmlentities($uri, ENT_QUOTES, 'utf-8');

        $response = $formRuntime->getResponse();

        $response->setContent('<html><head><meta http-equiv="refresh" content="' . $delay . ';url=' . $escapedUri . '"/></head></html>');
        $response->setStatus($statusCode);

        if ($delay === 0) {
            $response->setHeader('Location', (string)$uri);
        }
    }

    /**
     * @param array $options configuration options in the format array('@action' => 'foo', '@controller' => 'bar', '@package' => 'baz')
     * @return void
     * @api
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param ActionRequest $request
     * @return string
     */
    protected function buildActionUri(ActionRequest $request)
    {
        $packageKey = $this->parseOption('package');
        $controllerName = $this->parseOption('controller');
        $actionName = $this->parseOption('action');
        $arguments = $this->parseOption('arguments');

        $subpackageKey = null;
        if ($packageKey !== null && strpos($packageKey, '\\') !== false) {
            list($packageKey, $subpackageKey) = explode('\\', $packageKey, 2);
        }
        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($request);
        $uriBuilder->reset();

        $uri = $uriBuilder->uriFor($actionName, $arguments, $controllerName, $packageKey, $subpackageKey);
        return $uri;
    }
}
