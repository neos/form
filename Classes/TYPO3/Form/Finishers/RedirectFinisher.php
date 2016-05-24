<?php
namespace TYPO3\Form\Finishers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Routing\UriBuilder;
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
        $mainResponse = $response;
        while ($response = $response->getParentResponse()) {
            $mainResponse = $response;
        };
        $mainResponse->setContent('<html><head><meta http-equiv="refresh" content="' . $delay . ';url=' . $escapedUri . '"/></head></html>');
        $mainResponse->setStatus($statusCode);
        if ($delay === 0) {
            $mainResponse->setHeader('Location', (string)$uri);
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
