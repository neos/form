<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A Form
 */
class FormRuntime implements RenderableInterface {

	/**
	 * @var FormDefinition
	 */
	protected $formDefinition;

	/**
	 * @var \TYPO3\FLOW3\MVC\Web\Request
	 */
	protected $request;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\MVC\Web\Response
	 * @todo should be custom response class (tailored to forms) lateron
	 */
	protected $response;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\MVC\Web\SubRequestBuilder
	 */
	protected $subRequestBuilder;

	/**
	 * Workaround...
	 *
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\MVC\FlashMessageContainer
	 */
	protected $flashMessageContainer;

	/**
	 * @var \TYPO3\Form\Domain\Model\Page
	 */
	protected $currentPage = NULL;

	/**
	 * @var FormState
	 */
	protected $formState;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * @param FormDefinition $formDefinition
	 * @param \TYPO3\FLOW3\MVC\Web\Request $request
	 * @throws \TYPO3\Form\Exception\IdentifierNotValidException
	 * @internal
	 */
	public function __construct(FormDefinition $formDefinition, \TYPO3\FLOW3\MVC\Web\Request $request) {
		$this->formDefinition = $formDefinition;
		$this->request = $request;
	}

	public function initializeObject() {
		$this->request = $this->subRequestBuilder->build($this->request, $this->formDefinition->getIdentifier());
		$this->initializeCurrentPageFromRequest();
		$this->initializeFormStateFromRequest();
	}

	protected function initializeCurrentPageFromRequest() {
		$currentPageIndex = (integer)$this->request->getInternalArgument('__currentPage');
		$this->currentPage = $this->formDefinition->getPageByIndex($currentPageIndex);
		if ($this->currentPage === NULL) {
			$this->currentPage = $this->formDefinition->getPageByIndex(0);
		}
		// TODO: Exception if no page
	}

	protected function initializeFormStateFromRequest() {
		$serializedFormStateWithHmac = $this->request->getInternalArgument('__state');
		if ($serializedFormStateWithHmac === NULL) {
			$this->formState = new FormState();
		} else {
			$serializedFormState = $this->hashService->validateAndStripHmac($serializedFormStateWithHmac);
			$this->formState = unserialize(base64_decode($serializedFormState));
		}
	}

	/**
	 * @todo document
	 * @todo implement fully
	 */
	public function render() {
		$controllerContext = $this->getControllerContext();
		$view = new \TYPO3\Form\Domain\View\FluidRenderer();
		$view->setControllerContext($controllerContext);
		return $view->renderRenderable($this);
	}

	/**
	 * Returns the currently selected page
	 *
	 * @return \TYPO3\Form\Domain\Model\Page
	 */
	public function getCurrentPage() {
		return $this->currentPage;
	}

	/**
	 * Returns the previous page of the currently selected one or NULL if there is no previous page
	 *
	 * @return \TYPO3\Form\Domain\Model\Page
	 */
	public function getPreviousPage() {
		$currentPageIndex = $this->currentPage->getIndex();
		return $this->formDefinition->getPageByIndex($currentPageIndex - 1);
	}

	/**
	 * Returns the next page of the currently selected one or NULL if there is no next page
	 *
	 * @return \TYPO3\Form\Domain\Model\Page
	 */
	public function getNextPage() {
		$currentPageIndex = $this->currentPage->getIndex();
		return $this->formDefinition->getPageByIndex($currentPageIndex + 1);
	}

	protected function getControllerContext() {
		// TODO: build contoller context and return the same one always
		$uriBuilder = new \TYPO3\FLOW3\MVC\Web\Routing\UriBuilder();
		$uriBuilder->setRequest($this->request);

		return new \TYPO3\FLOW3\MVC\Controller\ControllerContext(
			$this->request,
			$this->response,
			new \TYPO3\FLOW3\MVC\Controller\Arguments(array()),
			$uriBuilder,
			$this->flashMessageContainer
		);
	}

	public function getType() {
		return 'TYPO3.Form:Form';
	}

	public function getTemplateVariableName() {
		return 'form';
	}

	public function offsetExists($offset) {
		return isset($this->elements[$offset]);
	}

	public function offsetGet($offset) {
		return (isset($this->elements[$offset]) ? $this->elements[$offset]->getValue() : NULL);
	}

	public function offsetSet($offset, $value) {
		$this->elements[$offset]->setValue($value);
	}

	public function offsetUnset($offset) {
		$this->elements[$offset]->setValue(NULL);
	}

	public function getPages() {
		return $this->formDefinition->getPages();
	}

	public function getFormState() {
		return $this->formState;
	}


}
?>