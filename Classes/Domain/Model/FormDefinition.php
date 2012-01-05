<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * This class encapsulates a complete *Form Definition*, with all of its pages,
 * form elements, validation rules which apply.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * It is *not modified* when the form executes.
 *
 * The Anatomy Of A Form
 * =====================
 *
 * A FormDefinition consists of multiple *Page* ({@link Page}) objects. When a
 * form is displayed to the user, only one *Page* is visible at any given time,
 * and there is a navigation to go back and forth between the pages.
 *
 * A *Page* consists of multiple *FormElements* ({@link FormElementInterface}, {@link AbstractFormElement}),
 * which represent the input fields, textareas, checkboxes shown inside the page.
 *
 * *FormDefinition*, *Page* and *FormElement* have *identifier* properties, which
 * must be unique for each given type (i.e. it is allowed that the FormDefinition and
 * a FormElement have the *same* identifier, but two FormElements are not allowed to
 * have the same identifier.
 *
 * Simple Example
 * --------------
 *
 * Generally, you can create a FormDefinition manually by just calling the API
 * methods on it, or you use a *Form Definition Factory* to build the form from
 * another representation format such as YAML.
 *
 * /---code php
 * $formDefinition = new FormDefinition('myForm');
 *
 * $page1 = new Page('myPage');
 * $formDefinition->addPage($page);
 *
 * $element1 = new GenericFormElement('title', 'TYPO3.Form:Text'); # the second argument is the type of the form element
 * $page1->addElement($element1);
 * \---
 *
 * Property Mapping and Validation Rules
 * =====================================
 *
 * Besides Pages and FormElements, the FormDefinition can contain information
 * about the *format of the data* which is inputted into the form. This generally means:
 *
 * - expected Data Types
 * - Property Mapping Configuration to be used
 * - Validation Rules which should apply
 *
 * > Background Info
 * > ---------------
 * > You might wonder why Data Types and Validation Rules are *not attached
 * > to each FormElement itself*.
 * >
 * > If the form should create a *hierarchical output structure* such as a multi-
 * > dimensional array or a PHP object, your expected data structure might look as follows:
 * > <pre>
 * > - person
 * > -- firstName
 * > -- lastName
 * > -- address
 * > --- street
 * > --- city
 * > </pre>
 * >
 * > Now, let's imagine you want to edit *person.address.street* and *person.address.city*,
 * > but want to validate that the *combination* of *street* and *city* is valid
 * > according to some address database.
 * >
 * > In this case, the form elements would be configured to fill *street* and *city*,
 * > but the *validator* needs to be attached to the *compound object* *address*,
 * > as both parts need to be
 *
 * Connecting FormElements to the output data structure
 * ====================================================
 *
 * The *identifier* of the *FormElement* is most important, as it determines
 * where in the output structure the value which is entered by the user is placed,
 * and thus also determines which validation rules need to apply.
 *
 * Using the above example, if you want to create a FormElement for the *street*,
 * you should use the identifier *person.address.street*.
 *
 * Rendering a FormDefinition
 * ==========================
 *
 * In order to trigger *rendering* on a FormDefinition,
 * the current {@link \TYPO3\FLOW3\MVC\Web\Request} needs to be bound to the FormDefinition,
 * resulting in a {@link FormRuntime} object which contains the *Runtime State* of the form
 * (such as the currently inserted values).
 *
 * /---code php
 * # $currentRequest needs to be available, f.e. inside a controller you would
 * # use $this->request; inside a ViewHelper you would use $this->controllerContext->getRequest()
 * $form = $formDefinition->bind($currentRequest);
 *
 * # now, you can use the $form object to get information about the currently
 * # entered values into the form, etc.
 * \---
 *
 * Refer to the {@link FormRuntime} API doc for further information.
 */
class FormDefinition {

	/**
	 * The identifier of this Form.
	 *
	 * @var string
	 * @internal
	 */
	protected $identifier;

	/**
	 * The pages this form is comprised of, in a numerically-indexed array
	 *
	 * @var array<TYPO3\Form\Domain\Model\Page>
	 * @internal
	 */
	protected $pages = array();

	/**
	 * Property Mapping Rules, indexed by element identifier
	 *
	 * @todo should this happen on page-level?
	 * @var array<TYPO3\Form\Domain\Model\MappingRule>
	 * @internal
	 */
	protected $mappingRules = array();

	/**
	 * Contains all elements of the form, indexed by identifier.
	 * Is used as internal cache as we need this really often.
	 *
	 * @var array <TYPO3\Form\Domain\Model\FormElementInterface>
	 * @internal
	 */
	protected $elementsByIdentifier = array();

	/**
	 * @var \TYPO3\Form\Utility\SupertypeResolver
	 * @internal
	 */
	protected $formFieldTypeManager;

	/**
	 * Constructor. Creates a new FormDefinition with the given identifier.
	 *
	 * @param string $identifier The Form Definition's identifier, must be a non-empty string.
	 * @return void
	 * @throws \TYPO3\Form\Exception\IdentifierNotValidException if the identifier was not valid
	 * @api
	 */
	public function __construct($identifier, $formDefaults = array()) {
		$this->formFieldTypeManager = new \TYPO3\Form\Utility\SupertypeResolver(isset($formDefaults['formElementTypes']) ? $formDefaults['formElementTypes'] : array());
		if (!is_string($identifier) || strlen($identifier) === 0) {
			throw new \TYPO3\Form\Exception\IdentifierNotValidException('The given identifier was not a string or the string was empty.', 1325574803);
		}
		$this->identifier = $identifier;
	}

	/**
	 * Returns the Form Definition's identifier
	 *
	 * @return string The Form Definition's identifier
	 * @api
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 *
	 * @param string $identifier
	 * @param string $typeName
	 * @return \TYPO3\Form\Domain\Model\Page
	 * @throws TYPO3\Form\Exception\TypeDefinitionNotValidException
	 * @api
	 */
	public function createPage($identifier, $typeName = 'TYPO3.Form:Page') {
		$typeDefinition = $this->formFieldTypeManager->getMergedTypeDefinition($typeName);

		if (!isset($typeDefinition['implementationClassName'])) {
			throw new \TYPO3\Form\Exception\TypeDefinitionNotFoundException(sprintf('The "implementationClassName" was not set in type definition "%s".', $typeName), 1325689855);
		}
		$implementationClassName = $typeDefinition['implementationClassName'];
		$page = new $implementationClassName($identifier);

		if (isset($typeDefinition['label'])) {
			$page->setLabel($typeDefinition['label']);
		}

		\TYPO3\Form\Utility\Arrays::assertAllArrayKeysAreValid($typeDefinition, array('implementationClassName', 'label'));

		$this->addPage($page);
		return $page;
	}

	/**
	 * Add a new page at the end of the form
	 *
	 * @param Page $page
	 * @throws \TYPO3\Form\Exception\FormDefinitionConsistencyException if Page is already added to a FormDefinition
	 * @api
	 */
	public function addPage(Page $page) {
		if ($page->getParentForm() !== NULL) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException(sprintf('The Page with identifier "%s" is already added to another form (form identifier: "%s").', $page->getIdentifier(), $page->getParentForm()->getIdentifier()), 1325665144);
		}

		$this->pages[] = $page;
		$page->setParentForm($this);
		$page->setIndex(count($this->pages) - 1);
		foreach ($page->getElements() as $element) {
			$this->addElementToElementsByIdentifierCache($element);
		}
	}

	/**
	 * Get the Form's pages
	 *
	 * @return array<TYPO3\Form\Domain\Model\Page> The Form's pages in the correct order
	 * @api
	 */
	public function getPages() {
		return $this->pages;
	}

	/**
	 * Get the page with the passed index. The first page has index zero.
	 *
	 * If index does not exist, returns NULL
	 *
	 * @param integer $index
	 * @return Page the page, or NULL if none found.
	 * @api
	 */
	public function getPageByIndex($index) {
		return isset($this->pages[$index]) ? $this->pages[$index] : NULL;
	}

	/**
	 * Add an element to the ElementsByIdentifier Cache.
	 *
	 * @param FormElementInterface $element
	 * @throws TYPO3\Form\Exception\DuplicateFormElementException
	 * @internal
	 * @todo rename to "registerElementInForm"
	 */
	public function addElementToElementsByIdentifierCache(FormElementInterface $element) {
		if (isset($this->elementsByIdentifier[$element->getIdentifier()])) {
			throw new \TYPO3\Form\Exception\DuplicateFormElementException(sprintf('A form element with identifier "%s" is already part of the form.', $element->getIdentifier()), 1325663761);
		}
		$this->elementsByIdentifier[$element->getIdentifier()] = $element;
	}

	/**
	 * Get an Element by its identifier
	 *
	 * If identifier does not exist, returns NULL.
	 *
	 * @param string $elementIdentifier
	 * @return FormElementInterface The element with the given $elementIdentifier or NULL if none found
	 * @api
	 */
	public function getElementByIdentifier($elementIdentifier) {
		return isset($this->elementsByIdentifier[$elementIdentifier]) ? $this->elementsByIdentifier[$elementIdentifier] : NULL;
	}

	/**
	 * Bind the current request to this form instance, effectively creating
	 * a new "instance" of the Form.
	 *
	 * @param \TYPO3\FLOW3\MVC\Web\Request $request
	 * @return \TYPO3\Form\Domain\Model\FormRuntime
	 * @api
	 */
	public function bind(\TYPO3\FLOW3\MVC\Web\Request $request) {
		return new FormRuntime($this, $request);
	}

	/**
	 * @param type $elementIdentifier
	 * @todo This might be a property path later on!!
	 * @todo Doc Comment
	 * @return MappingRule
	 */
	public function getMappingRule($elementIdentifier) {
		if (!isset($this->mappingRules[$elementIdentifier])) {
			$this->mappingRules[$elementIdentifier] = new MappingRule();
		}
		return $this->mappingRules[$elementIdentifier];
	}

	/**
	 * @return type
	 * @todo Doc Comment
	 */
	public function getMappingRules() {
		return $this->mappingRules;
	}

	/**
	 * @return \TYPO3\Form\Utility\SupertypeResolver
	 * @internal
	 */
	public function getFormFieldTypeManager() {
		return $this->formFieldTypeManager;
	}
}
?>