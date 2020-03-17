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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\BaseUriProvider;
use Neos\Flow\Http\Exception as HttpException;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Form\Core\Model\AbstractFinisher;
use Psr\Http\Message\UriFactoryInterface;

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
     * @Flow\Inject
     * @var UriFactoryInterface
     */
    protected $uriFactory;

    /**
     * @Flow\Inject
     * @var BaseUriProvider
     */
    protected $baseUriProvider;

    /**
     * Executes this finisher
     *
     * @return void
     * @throws MissingActionNameException
     * @throws HttpException
     * @see AbstractFinisher::execute()
     */
    public function executeInternal()
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $request = $formRuntime->getRequest()->getMainRequest();

        $delay = (int)$this->parseOption('delay');
        $statusCode = (int)$this->parseOption('statusCode');
        $uri = trim($this->parseOption('uri'));


        if ($uri === '') {
            $uri = $this->buildActionUri($request);
        }

        $uriParts = parse_url($uri);
        if (!isset($uriParts['scheme']) || $uriParts['scheme'] === '') {
            $uri = sprintf(
                '%s/%s',
                rtrim($this->baseUriProvider->getConfiguredBaseUriOrFallbackToCurrentRequest(), '/'),
                ltrim($uri, '/')
            );
        }

        $escapedUri = htmlentities($uri, ENT_QUOTES, 'utf-8');

        $response = $formRuntime->getResponse();

        $response->setContent('<html><head><meta http-equiv="refresh" content="' . $delay . ';url=' . $escapedUri . '"/></head></html>');
        $response->setStatusCode($statusCode);

        if ($delay === 0) {
            $response->setRedirectUri($this->uriFactory->createUri((string)$uri));
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
     * @throws MissingActionNameException
     */
    protected function buildActionUri(ActionRequest $request): string
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

        return $uriBuilder->uriFor($actionName, $arguments, $controllerName, $packageKey, $subpackageKey);
    }
}
