<?php
namespace TYPO3\Form\Domain\Model\Finisher;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use \TYPO3\Form\Domain\Model\FormRuntime;

/**
 * Finisher that can be attached to a form in order to be invoked
 * as soon as the complete form is submitted
 */
class RedirectFinisher implements FinisherInterface {

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * Executes the finisher for the
	 *
	 * @param \TYPO3\Form\Domain\Model\FormRuntime $formRuntime The Form runtime that invokes this finisher
	 * @return boolean TRUE by default, FALSE if invocation chain should be canceled
	 * @internal
	 */
	public function execute(FormRuntime $formRuntime) {
		$request = $formRuntime->getRequest()->getRootRequest();

		// TODO convenience function to merge options with "default options"
		$packageKey = isset($this->options['package']) ? $this->options['package'] : NULL;
		$actionName = isset($this->options['action']) ? $this->options['action'] : NULL;
		$arguments = isset($this->options['arguments']) ? $this->options['arguments'] : array();
		$controllerName = isset($this->options['controller']) ? $this->options['controller'] : NULL;
		$delay = isset($this->options['delay']) ? (integer)$this->options['delay'] : 0;
		$statusCode = isset($this->options['statusCode']) ? $this->options['statusCode'] : 303;

		if ($packageKey !== NULL && strpos($packageKey, '\\') !== FALSE) {
			list($packageKey, $subpackageKey) = explode('\\', $packageKey, 2);
		} else {
			$subpackageKey = NULL;
		}
		$uriBuilder = new \TYPO3\FLOW3\MVC\Web\Routing\UriBuilder();
		$uriBuilder->setRequest($request);
		$uriBuilder->reset();

		$uri = $uriBuilder->uriFor($actionName, $arguments, $controllerName, $packageKey, $subpackageKey);
		$uri = $request->getBaseUri() . $uri;
		$escapedUri = htmlentities($uri, ENT_QUOTES, 'utf-8');

		// TODO use response object
		echo '<html><head><meta http-equiv="refresh" content="' . $delay . ';url=' . $escapedUri . '"/></head></html>';
		header('HTTP/1.1 ' . $statusCode);
		if ($delay === 0) {
			header('Location: ' . (string)$uri);
		}
		exit;
	}

	/**
	 * @param array $options configuration options in the format array('@action' => 'foo', '@controller' => 'bar', '@package' => 'baz')
	 * @return void
	 * @api
	 */
	public function setOptions(array $options) {
		$this->options = $options;
	}
}
?>