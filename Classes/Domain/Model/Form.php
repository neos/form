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
	 * @var Page
	 */
	protected $currentPage = NULL;

	/**
	 * Constructor. Needs this Form's identifier
	 *
	 * @param string $identifier The Form's identifier
	 * @return void
	 * @api
	 */
	public function __construct($identifier) {
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
		$view->assign('form', $this);
		return $view->render('Form');
	}

	public function getCurrentPage() {
		$currentPageIndex = $this->request->getInternalArgument('__currentPage');
		if ($currentPageIndex) {
			$this->currentPage = $this->pages[intval($currentPageIndex)];
		}
		if ($this->currentPage === NULL) {
				// The first page is the default page
			return reset($this->pages);
		}
		return $this->currentPage;
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