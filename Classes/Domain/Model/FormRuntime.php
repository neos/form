<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * The form runtime
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * @todo Greatly expand documentation!
 */
class FormRuntime implements RenderableInterface, \ArrayAccess {

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
	 * @var \TYPO3\Form\Domain\Model\FormState
	 */
	protected $formState;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Property\PropertyMapper
	 */
	protected $propertyMapper;

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
		if ($currentPageIndex === count($this->formDefinition->getPages())) {
			$this->invokeFinishers();
		} else {
			$this->currentPage = $this->formDefinition->getPageByIndex($currentPageIndex);
			if ($this->currentPage === NULL) {
				$this->currentPage = $this->formDefinition->getPageByIndex(0);
			}
			// TODO: Exception if no page
		}
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
		$this->updateFormState();
		$controllerContext = $this->getControllerContext();
		$view = new \TYPO3\Form\Domain\View\FluidRenderer();
		$view->setControllerContext($controllerContext);
		return $view->renderRenderable($this);
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->formDefinition->getIdentifier();
	}

	protected function updateFormState() {
		if ($this->formState->isFormSubmitted()) {
			$lastDisplayedPage = $this->formDefinition->getPageByIndex($this->formState->getLastDisplayedPageIndex());

			$result = new \TYPO3\FLOW3\Error\Result();
			$mappingRules = $this->formDefinition->getMappingRules();
			foreach ($lastDisplayedPage->getElements() as $element) {
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
				$validator = $element->getValidator();

				$validationResult = $validator->validate($value);
				$result->forProperty($element->getIdentifier())->merge($validationResult);

				$this->formState->setFormValue($element->getIdentifier(), $value);
			}
			if ($result->hasErrors()) {
				$this->currentPage = $lastDisplayedPage;
				$this->request->setOriginalRequestMappingResults($result);
			}
			// TODO: Map arguments through property mapper
		}

		// Update currently shown page in FormState
		$this->formState->setLastDisplayedPageIndex($this->currentPage->getIndex());
	}

	/**
	 * Executes all finishers of this form
	 *
	 * @return void
	 */
	protected function invokeFinishers() {
		foreach ($this->formDefinition->getFinishers() as $finisher) {
			$finisherResult = $finisher->execute($this);
			if ($finisherResult !== TRUE) {
				break;
			}
		}
	}

	/**
	 * @return \TYPO3\FLOW3\MVC\Web\Request
	 */
	public function getRequest() {
		return $this->request;
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
	public function offsetGet($identifier) {
		return $this->getElementValue($identifier);
	}

	public function offsetSet($identifier, $value) {
		$this->formState->setFormValue($identifier, $value);
	}

	public function offsetUnset($identifier) {
		$this->formState->setFormValue($identifier, NULL);
	}

	public function getPages() {
		return $this->formDefinition->getPages();
	}

	public function getFormState() {
		return $this->formState;
	}


}
?>