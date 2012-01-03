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
class Form implements RenderableInterface, \ArrayAccess {

	/**
	 * The identifier
	 * @var string
	 */
	protected $identifier;

	/**
	 * The pages
	 * @var array<TYPO3\Form\Domain\Model\Page>
	 */
	protected $pages = array();

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
	 * Constructor. Needs this Form's identifier
	 *
	 * @param string $identifier The Form's identifier
	 * @return void
	 * @throws \TYPO3\Form\Exception\IdentifierNotValidException if the identifier was no non-empty string
	 * @api
	 */
	public function __construct($identifier) {
		if (!is_string($identifier) || strlen($identifier) === 0) {
			throw new \TYPO3\Form\Exception\IdentifierNotValidException('The given identifier was not a string or the string was empty.', 1325574803);
		}
		$this->identifier = $identifier;
	}

	/**
	 * Get the Form's identifier
	 *
	 * @return string The Form's identifier
	 * @api
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Get the Form's pages
	 *
	 * @return array<TYPO3\Form\Domain\Model\Page> The Form's pages
	 * @api
	 */
	public function getPages() {
		return $this->pages;
	}

	/**
	 * Add a new page at the end of the form
	 *
	 * @param Page $page
	 * @api
	 */
	public function addPage(Page $page) {
		$this->pages[] = $page;
		$page->setParentForm($this);
	}

	/**
	 * @param \TYPO3\FLOW3\MVC\Web\Request $request
	 * @api
	 * @todo request arguments rauspuhlen, current page befüllen
	 */
	public function bindRequest(\TYPO3\FLOW3\MVC\Web\Request $request) {
		$this->request = $this->subRequestBuilder->build($request, $this->identifier);
	}

	/**
	 * @todo document
	 * @todo implement fully
	 */
	public function render() {
		// TODO: exception if $this->pages is empty! ->extract to method "ensureThat...."

		$this->elements = array();
		foreach ($this->pages as $page) {
			foreach ($page->getElements() as $element) {
				// TODO: check for duplicates
				$this->elements[$element->getIdentifier()] = $element;
			}
		}

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
		if ($this->currentPage === NULL) {
			$currentPageIndex = $this->getCurrentPageIndex();
			$this->currentPage = isset($this->pages[$currentPageIndex]) ? $this->pages[$currentPageIndex] : reset($this->pages);
		}
		return $this->currentPage;
	}

	/**
	 * Returns the previous page of the currently selected one or NULL if there is no previous page
	 *
	 * @return \TYPO3\Form\Domain\Model\Page
	 */
	public function getPreviousPage() {
		$currentPageIndex = $this->getCurrentPageIndex();
		if ($currentPageIndex > 0 && isset($this->pages[$currentPageIndex - 1])) {
			return $this->pages[$currentPageIndex - 1];
		}
	}

	/**
	 * Returns the next page of the currently selected one or NULL if there is no next page
	 *
	 * @return \TYPO3\Form\Domain\Model\Page
	 */
	public function getNextPage() {
		$currentPageIndex = $this->getCurrentPageIndex();
		if (isset($this->pages[$currentPageIndex + 1])) {
			return $this->pages[$currentPageIndex + 1];
		}
	}

	/**
	 * Returns the (0 based) index of the current page.
	 * Defaults to zero
	 *
	 * @return integer
	 */
	protected function getCurrentPageIndex() {
		return (integer)$this->request->getInternalArgument('__currentPage');
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
}
?>