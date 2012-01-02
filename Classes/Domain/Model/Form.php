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
class Form {

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
		$this->request = $request;
		// TODO: Request-Arguments rauspuhlen (basierend auf identifier)
	}

	/**
	 * @todo document
	 * @todo implement fully
	 */
	public function render() {
		// TODO: exception if $this->pages is empty! ->extract to method "ensureThat...."
		if ($this->currentPage === NULL) {
				// The first page is the default page
			$this->currentPage = reset($this->pages);
		}
		$controllerContext = $this->getControllerContext();

		return $this->currentPage->render($controllerContext);
	}

	public function getControllerContext() {
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
}
?>