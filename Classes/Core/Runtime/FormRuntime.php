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
 * $form = $formDefinition->bind($request);
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
	 * @var FormDefinition
	 * @internal
	 */
	protected $formDefinition;

	/**
	 * @var \TYPO3\FLOW3\MVC\Web\Request
	 * @internal
	 */
	protected $request;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\MVC\Web\Response
	 * @todo should be custom response class (tailored to forms) lateron
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
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Property\PropertyMapper
	 * @internal
	 */
	protected $propertyMapper;

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
	 * @throws \TYPO3\Form\Exception\IdentifierNotValidException
	 * @internal
	 */
	public function __construct(\TYPO3\Form\Core\Model\FormDefinition $formDefinition, \TYPO3\FLOW3\MVC\Web\Request $request) {
		$this->formDefinition = $formDefinition;
		$this->request = $request;
	}

	/**
	 * @internal
	 */
	public function initializeObject() {
		$this->request = $this->subRequestBuilder->build($this->request, $this->formDefinition->getIdentifier());
		$this->initializeFormStateFromRequest();
		$this->initializeCurrentPageFromRequest();

		$this->processSubmittedFormValues();
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

	protected function isAfterLastPage() {
		return ($this->currentPage === NULL);
	}

	/**
	 * @internal
	 */
	protected function processSubmittedFormValues() {
		if ($this->lastDisplayedPage !== NULL) {
			$result = $this->mapAndValidatePage($this->lastDisplayedPage);
			if ($result->hasErrors()) {
				$this->currentPage = $this->lastDisplayedPage;
				$this->request->setOriginalRequestMappingResults($result);
			}
		}
	}

	/**
	 * @param \TYPO3\Form\Core\Model\Page $page
	 * @return \TYPO3\FLOW3\Error\Result
	 * @internal
	 */
	protected function mapAndValidatePage(\TYPO3\Form\Core\Model\Page $page) {

		// TODO: Map arguments through property mapper
		$result = new \TYPO3\FLOW3\Error\Result();
		$mappingRules = $this->formDefinition->getMappingRules();
		foreach ($page->getElementsRecursively() as $element) {
			$value = NULL;
			if ($this->request->hasArgument($element->getIdentifier())) {
				$value = $this->request->getArgument($element->getIdentifier());
				if (isset($mappingRules[$element->getIdentifier()])) {
					$mappingRule = $mappingRules[$element->getIdentifier()];
					$value = $this->propertyMapper->convert($value, $mappingRule->getDataType(), $mappingRule->getPropertyMappingConfiguration());
					$result->forProperty($element->getIdentifier())->merge($this->propertyMapper->getMessages());
				}
			}

				// TODO: support "." syntax (property paths, maybe through the Property Mapper)
				// TODO: Sections are not supported yet (elements inside sections are not validated!)
			$validator = $element->getValidator();

			$validationResult = $validator->validate($value);
			$result->forProperty($element->getIdentifier())->merge($validationResult);

			$this->formState->setFormValue($element->getIdentifier(), $value);
		}
		return $result;
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
			// TODO: use request / response properly
			return;
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
		return $renderer->renderRenderable($this);
	}

	/**
	 * Executes all finishers of this form
	 *
	 * @return void
	 * @internal
	 */
	protected function invokeFinishers() {
		foreach ($this->formDefinition->getFinishers() as $finisher) {
			$finisherResult = $finisher->execute($this);
			// TODO: clean up, introduce event object or so...
			if ($finisherResult !== TRUE) {
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
	 * @param type $identifier
	 * @return type
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
	 *
	 * @param type $identifier
	 * @return type
	 * @api
	 */
	public function offsetGet($identifier) {
		return $this->getElementValue($identifier);
	}

	/**
	 * @api
	 */
	public function offsetSet($identifier, $value) {
		$this->formState->setFormValue($identifier, $value);
	}

	/**
	 * @api
	 */
	public function offsetUnset($identifier) {
		$this->formState->setFormValue($identifier, NULL);
	}

	/**
	 * @api
	 */
	public function getPages() {
		return $this->formDefinition->getPages();
	}

	/**
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
		$this->formDefinition->getLabel();
	}
}
?>