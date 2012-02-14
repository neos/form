<?php
namespace TYPO3\Form\Core\Runtime;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * This class implements the *runtime logic* of a form, i.e. deciding which
 * page is shown currently, what the current values of the form are, trigger
 * validation and property mapping.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * You generally receive an instance of this class by calling {@link \TYPO3\Form\Core\Model\FormDefinition::bind}.
 *
 * Rendering a Form
 * ================
 *
 * That's easy, just call render() on the FormRuntime:
 *
 * /---code php
 * $form = $formDefinition->bind($request, $response);
 * $renderedForm = $form->render();
 * \---
 *
 * Accessing Form Values
 * =====================
 *
 * In order to get the values the user has entered into the form, you can access
 * this object like an array: If a form field with the identifier *firstName*
 * exists, you can do **$form['firstName']** to retrieve its current value.
 *
 * You can also set values in the same way.
 *
 * Rendering Internals
 * ===================
 *
 * The FormRuntime asks the FormDefinition about the configured Renderer
 * which should be used ({@link \TYPO3\Form\Core\Model\FormDefinition::getRendererClassName}),
 * and then trigger render() on this element.
 *
 * This makes it possible to declaratively define how a form should be rendered.
 *
 * @api
 */
class FormRuntime implements \TYPO3\Form\Core\Model\Renderable\RootRenderableInterface, \ArrayAccess {

	/**
	 * @var \TYPO3\Form\Core\Model\FormDefinition
	 * @internal
	 */
	protected $formDefinition;

	/**
	 * @var \TYPO3\FLOW3\MVC\Web\Request
	 * @internal
	 */
	protected $request;

	/**
	 * @var \TYPO3\FLOW3\MVC\Web\Response
	 * @internal
	 */
	protected $response;

	/**
	 * @var \TYPO3\Form\Core\Runtime\FormState
	 * @internal
	 */
	protected $formState;

	/**
	 * The current page is the page which will be displayed to the user
	 * during rendering.
	 *
	 * If $currentPage is NULL, the *last* page has been submitted and
	 * finishing actions need to take place. You should use $this->isAfterLastPage()
	 * instead of explicitely checking for NULL.
	 *
	 * @var \TYPO3\Form\Core\Model\Page
	 * @internal
	 */
	protected $currentPage = NULL;

	/**
	 * Reference to the page which has been shown on the last request (i.e.
	 * we have to handle the submitted data from lastDisplayedPage)
	 *
	 * @var \TYPO3\Form\Core\Model\Page
	 * @internal
	 */
	protected $lastDisplayedPage = NULL;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\MVC\Web\SubRequestBuilder
	 * @internal
	 */
	protected $subRequestBuilder;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\Cryptography\HashService
	 * @internal
	 */
	protected $hashService;

	/**
	 * Workaround...
	 *
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\MVC\FlashMessageContainer
	 * @internal
	 */
	protected $flashMessageContainer;

	/**
	 * @param \TYPO3\Form\Core\Model\FormDefinition $formDefinition
	 * @param \TYPO3\FLOW3\MVC\Web\Request $request
	 * @param \TYPO3\FLOW3\MVC\Web\Response $response
	 * @throws \TYPO3\Form\Exception\IdentifierNotValidException
	 * @internal
	 */
	public function __construct(\TYPO3\Form\Core\Model\FormDefinition $formDefinition, \TYPO3\FLOW3\MVC\Web\Request $request, \TYPO3\FLOW3\MVC\Web\Response $response) {
		$this->formDefinition = $formDefinition;
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * @internal
	 */
	public function initializeObject() {
		$this->request = $this->subRequestBuilder->build($this->request, $this->formDefinition->getIdentifier());
		$this->initializeFormStateFromRequest();
		$this->initializeCurrentPageFromRequest();

		if ($this->formPageHasBeenSubmitted()) {
			$this->processSubmittedFormValues();
		}
	}

	/**
	 * @internal
	 */
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
	 * @internal
	 */
	protected function initializeCurrentPageFromRequest() {
		if (!$this->formState->isFormSubmitted()) {
			$this->currentPage = $this->formDefinition->getPageByIndex(0);
			return;
		}
		$this->lastDisplayedPage = $this->formDefinition->getPageByIndex($this->formState->getLastDisplayedPageIndex());

		// We know now that lastDisplayedPage is filled
		$currentPageIndex = (integer)$this->request->getInternalArgument('__currentPage');
		if ($currentPageIndex > $this->lastDisplayedPage->getIndex() + 1) {
				// We only allow jumps to following pages
			$currentPageIndex = $this->lastDisplayedPage->getIndex() + 1;
		}

		// We now know that the user did not try to skip a page
		if ($currentPageIndex === count($this->formDefinition->getPages())) {
				// Last Page
			$this->currentPage = NULL;
		} else {
			$this->currentPage = $this->formDefinition->getPageByIndex($currentPageIndex);
		}
	}

	/**
	 * Returns TRUE if the last page of the form has been submitted, otherwise FALSE
	 *
	 * @return boolean
	 */
	protected function isAfterLastPage() {
		return ($this->currentPage === NULL);
	}

	/**
	 * Returns TRUE if no previous page is stored in the FormState, otherwise FALSE
	 *
	 * @return boolean
	 */
	protected function isFirstRequest() {
		return ($this->lastDisplayedPage === NULL);
	}

	/**
	 * Returns TRUE if a previous form page has been submitted, otherwise FALSE
	 *
	 * @return boolean
	 * @todo find a better name?
	 */
	protected function formPageHasBeenSubmitted() {
		if ($this->isFirstRequest()) {
			return FALSE;
		}
		if ($this->isAfterLastPage()) {
			return TRUE;
		}
		return $this->lastDisplayedPage->getIndex() < $this->currentPage->getIndex();
	}

	/**
	 * @internal
	 */
	protected function processSubmittedFormValues() {
		$result = $this->mapAndValidatePage($this->lastDisplayedPage);
		if ($result->hasErrors()) {
			$this->currentPage = $this->lastDisplayedPage;
			$this->request->setOriginalRequestMappingResults($result);
		}
	}

	/**
	 * @param \TYPO3\Form\Core\Model\Page $page
	 * @return \TYPO3\FLOW3\Error\Result
	 * @internal
	 */
	protected function mapAndValidatePage(\TYPO3\Form\Core\Model\Page $page) {
		$result = new \TYPO3\FLOW3\Error\Result();
		$processingRules = $this->formDefinition->getProcessingRules();

		$requestArguments = $this->request->getArguments();

		$propertyPathsForWhichPropertyMappingShouldHappen = array();
		$registerPropertyPaths = function($propertyPath) use (&$propertyPathsForWhichPropertyMappingShouldHappen) {
			$propertyPathParts = explode ('.', $propertyPath);
			$accumulatedPropertyPathParts = array();
			foreach ($propertyPathParts as $propertyPathPart) {
				$accumulatedPropertyPathParts[] = $propertyPathPart;
				$temporaryPropertyPath = implode('.', $accumulatedPropertyPathParts);
				$propertyPathsForWhichPropertyMappingShouldHappen[$temporaryPropertyPath] = $temporaryPropertyPath;
			}
		};

		foreach ($page->getElementsRecursively() as $element) {
			$value = \TYPO3\FLOW3\Utility\Arrays::getValueByPath($requestArguments, $element->getIdentifier());
			$element->onSubmit($this, $value);

			// TODO: should this be set on FormState or not??
			$this->formState->setFormValue($element->getIdentifier(), $value);
			$registerPropertyPaths($element->getIdentifier());
		}

		// The more parts the path has, the more early it is processed
		usort($propertyPathsForWhichPropertyMappingShouldHappen, function($a, $b) {
			return substr_count($b, '.') - substr_count($a, '.');
		});

		foreach ($propertyPathsForWhichPropertyMappingShouldHappen as $propertyPath) {
			if (isset($processingRules[$propertyPath])) {
				$processingRule = $processingRules[$propertyPath];
				$value = $this->formState->getFormValue($propertyPath);
				$value = $processingRule->process($value);
				$result->forProperty($propertyPath)->merge($processingRule->getProcessingMessages());
				$this->formState->setFormValue($propertyPath, $value);
			}
		}

		return $result;
	}

	/**
	 * Override the current page taken from the request, rendering the page with index $pageIndex instead.
	 *
	 * This is typically not needed in production code, but it is very helpful when displaying
	 * some kind of "preview" of the form.
	 *
	 * @param integer $pageIndex
	 * @api
	 */
	public function overrideCurrentPage($pageIndex) {
		$this->currentPage = $this->formDefinition->getPageByIndex($pageIndex);
	}

	/**
	 * Render this form.
	 *
	 * @return string rendered form
	 * @api
	 */
	public function render() {
		if ($this->isAfterLastPage()) {
			$this->invokeFinishers();
			return $this->response->getContent();
		}

		$this->formState->setLastDisplayedPageIndex($this->currentPage->getIndex());

		if ($this->formDefinition->getRendererClassName() === NULL) {
			throw new \TYPO3\Form\Exception\RenderingException(sprintf('The form definition "%s" does not have a rendererClassName set.', $this->formDefinition->getIdentifier()), 1326095912);
		}
		$rendererClassName = $this->formDefinition->getRendererClassName();
		$renderer = new $rendererClassName();
		if (!($renderer instanceof \TYPO3\Form\Core\Renderer\RendererInterface)) {
			throw new \TYPO3\Form\Exception\RenderingException(sprintf('The renderer "%s" des not implement RendererInterface', $rendererClassName), 1326096024);
		}

		$controllerContext = $this->getControllerContext();
		$renderer->setControllerContext($controllerContext);
		// TODO: use request / response properly
		$renderer->setFormRuntime($this);
		return $renderer->renderRenderable($this);
	}

	/**
	 * Executes all finishers of this form
	 *
	 * @return void
	 * @internal
	 */
	protected function invokeFinishers() {
		$finisherContext = new \TYPO3\Form\Core\Model\FinisherContext($this);
		foreach ($this->formDefinition->getFinishers() as $finisher) {
			$finisher->execute($finisherContext);
			if ($finisherContext->isCancelled()) {
				break;
			}
		}
	}

	/**
	 * @return string The identifier of underlying form
	 * @api
	 */
	public function getIdentifier() {
		return $this->formDefinition->getIdentifier();
	}

	/**
	 * Get the request this object is bound to.
	 *
	 * This is mostly relevant inside Finishers, where you f.e. want to redirect
	 * the user to another page.
	 *
	 * @return \TYPO3\FLOW3\MVC\Web\Request the request this object is bound to
	 * @api
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Get the response this object is bound to.
	 *
	 * This is mostly relevant inside Finishers, where you f.e. want to set response
	 * headers or output content.
	 *
	 * @return \TYPO3\FLOW3\MVC\Web\Response the response this object is bound to
	 * @api
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * Returns the currently selected page
	 *
	 * @return \TYPO3\Form\Core\Model\Page
	 * @api
	 */
	public function getCurrentPage() {
		return $this->currentPage;
	}

	/**
	 * Returns the previous page of the currently selected one or NULL if there is no previous page
	 *
	 * @return \TYPO3\Form\Core\Model\Page
	 * @api
	 */
	public function getPreviousPage() {
		$currentPageIndex = $this->currentPage->getIndex();
		return $this->formDefinition->getPageByIndex($currentPageIndex - 1);
	}

	/**
	 * Returns the next page of the currently selected one or NULL if there is no next page
	 *
	 * @return \TYPO3\Form\Core\Model\Page
	 * @api
	 */
	public function getNextPage() {
		$currentPageIndex = $this->currentPage->getIndex();
		return $this->formDefinition->getPageByIndex($currentPageIndex + 1);
	}

	/**
	 *
	 * @return \TYPO3\FLOW3\MVC\Controller\ControllerContext
	 * @internal
	 */
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
		return $this->formDefinition->getType();
	}

	/**
	 * @param string $identifier
	 * @return mixed
	 * @api
	 */
	public function offsetExists($identifier) {
		return ($this->getElementValue($identifier) !== NULL);
	}

	protected function getElementValue($identifier) {
		$formValue = $this->formState->getFormValue($identifier);
		if ($formValue !== NULL) {
			return $formValue;
		}
		$formElement = $this->formDefinition->getElementByIdentifier($identifier);
		if ($formElement !== NULL) {
			return $formElement->getDefaultValue();
		}
		return NULL;

	}

	/**
	 * @param string $identifier
	 * @return mixed
	 * @api
	 */
	public function offsetGet($identifier) {
		return $this->getElementValue($identifier);
	}

	/**
	 * @param string $identifier
	 * @param mixed $value
	 * @return void
	 * @api
	 */
	public function offsetSet($identifier, $value) {
		$this->formState->setFormValue($identifier, $value);
	}

	/**
	 * @api
	 * @param string $identifier
	 * @return void
	 */
	public function offsetUnset($identifier) {
		$this->formState->setFormValue($identifier, NULL);
	}

	/**
	 * @return array<TYPO3\Form\Core\Model\Page> The Form's pages in the correct order
	 * @api
	 */
	public function getPages() {
		return $this->formDefinition->getPages();
	}

	/**
	 * @return \TYPO3\Form\Core\Runtime\FormState
	 * @internal
	 */
	public function getFormState() {
		return $this->formState;
	}

	public function getRenderingOptions() {
		return $this->formDefinition->getRenderingOptions();
	}

	public function getRendererClassName() {
		return $this->formDefinition->getRendererClassName();
	}

	public function getLabel() {
		return $this->formDefinition->getLabel();
	}

	public function beforeRendering(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime) {
	}
}
?>