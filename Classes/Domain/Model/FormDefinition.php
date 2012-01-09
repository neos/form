<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;
use TYPO3\Form\Domain\Model\Finisher\FinisherInterface;

/**
 * This class encapsulates a complete *Form Definition*, with all of its pages,
 * form elements, validation rules which apply and finishers which should be
 * executed when the form is completely filled in.
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
 * $element1 = new GenericFormElement('title', 'TYPO3.Form:Textfield'); # the second argument is the type of the form element
 * $page1->addElement($element1);
 * \---
 *
 * Creating a Form, Using Abstract Form Element Types
 * =====================================================
 *
 * While you can use the {@link FormDefinition::addPage} or {@link Page::addElement}
 * methods and create the Page and FormElement objects manually, it is often better
 * to use the corresponding create* methods ({@link FormDefinition::createPage}
 * and {@link Page::createElement}), as you pass them an abstract *Form Element Type*
 * such as *TYPO3.Form:Text* or *TYPO3.Form.Page*, and the system **automatically
 * resolves the implementation class name and sets default values**.
 *
 * So the simple example from above should be rewritten as follows:
 *
 * /---code php
 * $formDefaults = array(); // We'll talk about this later
 *
 * $formDefinition = new FormDefinition('myForm', $formDefaults);
 * $page1 = $formDefinition->createPage('myPage');
 * $element1 = $page1->addElement('title', 'TYPO3.Form:Textfield');
 * \---
 *
 * Now, you might wonder how the system knows that the element *TYPO3.Form:Textfield*
 * is implemented using a GenericFormElement: **This is configured in the $formDefaults**.
 *
 * To make the example from above actually work, we need to add some sensible
 * values to *$formDefaults*:
 *
 * <pre>
 * $formDefaults = array(
 *   'formElementTypes' => array(
 *     'TYPO3.Form:Page' => array(
 *       'implementationClassName' => 'TYPO3\Form\Domain\Model\Page'
 *     ),
 *     'TYPO3.Form:Textfield' => array(
 *       'implementationClassName' => 'TYPO3\Form\Domain\Model\GenericFormElement'
 *     )
 *   )
 * )
 * </pre>
 *
 * For each abstract *Form Element Type* we add some configuration; in the above
 * case only the *implementation class name*. Still, it is possible to set defaults
 * for *all* configuration options of such an element, as the following example
 * shows:
 *
 * <pre>
 * $formDefaults = array(
 *   'formElementTypes' => array(
 *     'TYPO3.Form:Page' => array(
 *       'implementationClassName' => 'TYPO3\Form\Domain\Model\Page',
 *       'label' => 'this is the label of the page if nothing is specified'
 *     ),
 *     'TYPO3.Form:Textfield' => array(
 *       'implementationClassName' => 'TYPO3\Form\Domain\Model\GenericFormElement',
 *       'label' = >'Default Label',
 *       'defaultValue' => 'Default form element value',
 *       'properties' => array(
 *         'placeholder' => 'Text which is shown if element is empty'
 *       )
 *     )
 *   )
 * )
 * </pre>
 *
 * Introducing Supertypes
 * ----------------------
 *
 * Some form elements like the *Text* field and the *Date* field have a lot in common,
 * and only differ in a few different default values. In order to reduce the typing
 * overhead, it is possible to specify a list of **superTypes** which are used as a
 * basis:
 *
 * <pre>
 * $formDefaults = array(
 *   'formElementTypes' => array(
 *     'TYPO3.Form:Base' => array(
 *       'implementationClassName' => 'TYPO3\Form\Domain\Model\GenericFormElement',
 *       'label' = >'Default Label'
 *     ),
 *     'TYPO3.Form:Textfield' => array(
 *       'superTypes' => array('TYPO3.Form:Base'),
 *       'defaultValue' => 'Default form element value',
 *       'properties' => array(
 *         'placeholder' => 'Text which is shown if element is empty'
 *       )
 *     )
 *   )
 * )
 * </pre>
 *
 * Here, we specified that the *Textfield* uses *TYPO3.Form:Base* as **supertype**,
 * which can reduce typing overhead a lot. It is also possible to use *multiple
 * supertypes*, which are then evaluated in the order in which they are specified.
 *
 * Supertypes are evaluated recursively.
 *
 * Thus, default values are merged in the following order, while later values
 * override prior ones:
 *
 * - configuration of 1st supertype
 * - configuration of 2nd supertype
 * - configuration of ... supertype
 * - configuration of the type itself
 *
 * Using Preconfigured $formDefaults
 * ---------------------------------
 *
 * Often, it is not really useful to manually create the $formDefaults array.
 *
 * Most of it comes pre-configured inside the *TYPO3.Form* package's **Settings.yaml**,
 * and the {@link \TYPO3\Form\Domain\Factory\AbstractFormFactory} contains helper methods
 * which return the ready-to-use *$formConfiguration*. Please read the documentation
 * on {@link \TYPO3\Form\Domain\Factory\AbstractFormFactory} for some best-practice
 * usage examples.
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
class FormDefinition extends AbstractRenderable {

	/**
	 * The pages this form is comprised of, in a numerically-indexed array
	 *
	 * @var array<TYPO3\Form\Domain\Model\Page>
	 * @internal
	 */
	protected $pages = array();

	/**
	 * The finishers for this form
	 *
	 * @var array<TYPO3\Form\Domain\Model\Finisher\FinisherInterface>
	 * @internal
	 */
	protected $finishers = array();

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
	public function __construct($identifier, $formDefaults = array(), $type = 'TYPO3.Form:Form') {
		$this->formFieldTypeManager = new \TYPO3\Form\Utility\SupertypeResolver(isset($formDefaults['formElementTypes']) ? $formDefaults['formElementTypes'] : array());
		if (!is_string($identifier) || strlen($identifier) === 0) {
			throw new \TYPO3\Form\Exception\IdentifierNotValidException('The given identifier was not a string or the string was empty.', 1325574803);
		}
		$this->identifier = $identifier;
		$this->type = $type;

		if ($formDefaults !== array()) {
			$this->initializeFromFormDefaults();
		}
	}

	protected function initializeFromFormDefaults() {
		$typeDefinition = $this->formFieldTypeManager->getMergedTypeDefinition($this->type);
		if (isset($typeDefinition['renderingOptions'])) {
			foreach ($typeDefinition['renderingOptions'] as $key => $value) {
				$this->setRenderingOption($key, $value);
			}
		}

		\TYPO3\Form\Utility\Arrays::assertAllArrayKeysAreValid($typeDefinition, array('renderingOptions'));
	}

	/**
	 * Create a page with the given $identifier and attach this page to the form.
	 *
	 * - Create Page object based on the given $typeName
	 * - set defaults inside the Page object
	 * - attach Page object to this form
	 * - return the newly created Page object
	 *
	 * @param string $identifier Identifier of the new page
	 * @param string $typeName Type of the new page
	 * @return \TYPO3\Form\Domain\Model\Page the newly created page
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

		if (isset($typeDefinition['renderingOptions'])) {
			foreach ($typeDefinition['renderingOptions'] as $key => $value) {
				$page->setRenderingOption($key, $value);
			}
		}

		\TYPO3\Form\Utility\Arrays::assertAllArrayKeysAreValid($typeDefinition, array('implementationClassName', 'label', 'renderingOptions'));

		$this->addPage($page);
		return $page;
	}

	/**
	 * Add a new page at the end of the form.
	 *
	 * Instead of this method, you should often use {@link createPage} instead.
	 *
	 * @param Page $page
	 * @throws \TYPO3\Form\Exception\FormDefinitionConsistencyException if Page is already added to a FormDefinition
	 * @see createPage
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
	 * Adds the specified finisher to this form
	 *
	 * @param \TYPO3\Form\Domain\Model\Finisher\FinisherInterface $finisher
	 * @return void
	 * @api
	 */
	public function addFinisher(FinisherInterface $finisher) {
		$this->finishers[] = $finisher;
	}

	/**
	 * Gets all finishers of this form
	 *
	 * @return array<\TYPO3\Form\Domain\Model\Finisher\FinisherInterface>
	 * @api
	 */
	public function getFinishers() {
		return $this->finishers;
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
	 * Remove an element from the ElementsByIdentifier cache
	 *
	 * @param FormElementInterface $element
	 * @internal
	 */
	public function removeElementFromElementsByIdentifierCache(FormElementInterface $element) {
		unset($this->elementsByIdentifier[$element->getIdentifier()]);
	}

	/**
	 * Get a Form Element by its identifier
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
	 * Move $pageToMove before $referencePage
	 *
	 * @param Page $pageToMove
	 * @param Page $referencePage
	 * @api
	 */
	public function movePageBefore(Page $pageToMove, Page $referencePage) {
		if ($pageToMove->getParentForm() !== $referencePage->getParentForm() || $pageToMove->getParentForm() !== $this) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException('Moved pages need to be parts of the same form.', 1326089744);
		}

		$reorderedPages = array();
		$i = 0;
		foreach ($this->pages as $page) {
			if ($page === $pageToMove) continue;

			if ($page === $referencePage) {
				$reorderedPages[] = $pageToMove;
				$pageToMove->setIndex($i);
				$i++;
			}
			$reorderedPages[] = $page;
			$page->setIndex($i);
			$i++;
		}
		$this->pages = $reorderedPages;
	}

	/**
	 * Move $pageToMove after $referencePage
	 *
	 * @param Page $pageToMove
	 * @param Page $referencePage
	 * @api
	 */
	public function movePageAfter(Page $pageToMove, Page $referencePage) {
		if ($pageToMove->getParentForm() !== $referencePage->getParentForm() || $pageToMove->getParentForm() !== $this) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException('Moved pages need to be parts of the same form.', 1326089756);
		}

		$reorderedPages = array();
		$i = 0;
		foreach ($this->pages as $page) {
			if ($page === $pageToMove) continue;

			$reorderedPages[] = $page;
			$page->setIndex($i);
			$i++;

			if ($page === $referencePage) {
				$reorderedPages[] = $pageToMove;
				$pageToMove->setIndex($i);
				$i++;
			}
		}
		$this->pages = $reorderedPages;
	}

	/**
	 * Remove $pageToRemove from form
	 *
	 * @param Page $pageToRemove
	 * @api
	 */
	public function removePage(Page $pageToRemove) {
		if ($pageToRemove->getParentForm() !== $this) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException('The page to be removed must be part of the given form.', 1326090127);
		}

		$updatedPages = array();
		foreach ($this->pages as $page) {
			if ($page === $pageToRemove) continue;

			$updatedPages[] = $page;
		}
		$this->pages = $updatedPages;

		$pageToRemove->setParentForm(NULL);

		foreach ($pageToRemove->getElements() as $element) {
			$this->removeElementFromElementsByIdentifierCache($element);
		}
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