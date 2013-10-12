<?php
namespace TYPO3\Form\Core\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

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
 * $page1 = new Page('page1');
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
 * $page1 = $formDefinition->createPage('page1');
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
 *       'implementationClassName' => 'TYPO3\Form\Core\Model\Page'
 *     ),
 *     'TYPO3.Form:Textfield' => array(
 *       'implementationClassName' => 'TYPO3\Form\Core\Model\GenericFormElement'
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
 *       'implementationClassName' => 'TYPO3\Form\Core\Model\Page',
 *       'label' => 'this is the label of the page if nothing is specified'
 *     ),
 *     'TYPO3.Form:Textfield' => array(
 *       'implementationClassName' => 'TYPO3\Form\Core\Model\GenericFormElement',
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
 *       'implementationClassName' => 'TYPO3\Form\Core\Model\GenericFormElement',
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
 * and the {@link \TYPO3\Form\Factory\AbstractFormFactory} contains helper methods
 * which return the ready-to-use *$formDefaults*. Please read the documentation
 * on {@link \TYPO3\Form\Factory\AbstractFormFactory} for some best-practice
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
 * Background Info
 * ---------------
 * You might wonder why Data Types and Validation Rules are *not attached
 * to each FormElement itself*.
 *
 * If the form should create a *hierarchical output structure* such as a multi-
 * dimensional array or a PHP object, your expected data structure might look as follows:
 * <pre>
 * - person
 * -- firstName
 * -- lastName
 * -- address
 * --- street
 * --- city
 * </pre>
 *
 * Now, let's imagine you want to edit *person.address.street* and *person.address.city*,
 * but want to validate that the *combination* of *street* and *city* is valid
 * according to some address database.
 *
 * In this case, the form elements would be configured to fill *street* and *city*,
 * but the *validator* needs to be attached to the *compound object* *address*,
 * as both parts need to be validated together.
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
 * the current {@link \TYPO3\Flow\Mvc\ActionRequest} needs to be bound to the FormDefinition,
 * resulting in a {@link \TYPO3\Form\Core\Runtime\FormRuntime} object which contains the *Runtime State* of the form
 * (such as the currently inserted values).
 *
 * /---code php
 * # $currentRequest and $currentResponse need to be available, f.e. inside a controller you would
 * # use $this->request and $this->response; inside a ViewHelper you would use $this->controllerContext->getRequest()
 * # and $this->controllerContext->getResponse()
 * $form = $formDefinition->bind($currentRequest, $currentResponse);
 *
 * # now, you can use the $form object to get information about the currently
 * # entered values into the form, etc.
 * \---
 *
 * Refer to the {@link \TYPO3\Form\Core\Runtime\FormRuntime} API doc for further information.
 */
class FormDefinition extends Renderable\AbstractCompositeRenderable {

	/**
	 * The finishers for this form
	 *
	 * @var array<TYPO3\Form\Core\Model\FinisherInterface>
	 * @internal
	 */
	protected $finishers = array();

	/**
	 * Property Mapping Rules, indexed by element identifier
	 *
	 * @var array<TYPO3\Form\Core\Model\ProcessingRule>
	 * @internal
	 */
	protected $processingRules = array();

	/**
	 * Contains all elements of the form, indexed by identifier.
	 * Is used as internal cache as we need this really often.
	 *
	 * @var array <TYPO3\Form\Core\Model\FormElementInterface>
	 * @internal
	 */
	protected $elementsByIdentifier = array();

	/**
	 * Form element default values in the format array('elementIdentifier' => 'default value')
	 *
	 * @var array
	 * @internal
	 */
	protected $elementDefaultValues = array();

	/**
	 * @var \TYPO3\Form\Utility\SupertypeResolver
	 * @internal
	 */
	protected $formFieldTypeManager;

	/**
	 * @var array
	 * @internal
	 */
	protected $validatorPresets;

	/**
	 * @var array
	 * @internal
	 */
	protected $finisherPresets;

	/**
	 * Constructor. Creates a new FormDefinition with the given identifier.
	 *
	 * @param string $identifier The Form Definition's identifier, must be a non-empty string.
	 * @param array $formDefaults overrides form defaults of this definition
	 * @param string $type element type of this form in the format Package:Type
	 * @throws \TYPO3\Form\Exception\IdentifierNotValidException if the identifier was not valid
	 * @api
	 */
	public function __construct($identifier, $formDefaults = array(), $type = 'TYPO3.Form:Form') {
		$this->formFieldTypeManager = new \TYPO3\Form\Utility\SupertypeResolver(isset($formDefaults['formElementTypes']) ? $formDefaults['formElementTypes'] : array());
		$this->validatorPresets = isset($formDefaults['validatorPresets']) ? $formDefaults['validatorPresets'] : array();
		$this->finisherPresets = isset($formDefaults['finisherPresets']) ? $formDefaults['finisherPresets'] : array();

		if (!is_string($identifier) || strlen($identifier) === 0) {
			throw new \TYPO3\Form\Exception\IdentifierNotValidException('The given identifier was not a string or the string was empty.', 1325574803);
		}
		$this->identifier = $identifier;
		$this->type = $type;

		if ($formDefaults !== array()) {
			$this->initializeFromFormDefaults();
		}
	}

	/**
	 * Initialize the form defaults of the current type
	 *
	 * @return void
	 * @internal
	 */
	protected function initializeFromFormDefaults() {
		$typeDefinition = $this->formFieldTypeManager->getMergedTypeDefinition($this->type);
		$this->setOptions($typeDefinition);
	}

	/**
	 * Set multiple properties of this object at once.
	 * Every property which has a corresponding set* method can be set using
	 * the passed $options array.
	 *
	 * @param array $options
	 * @return void
	 * @internal
	 */
	public function setOptions(array $options) {
		if (isset($options['rendererClassName'])) {
			$this->setRendererClassName($options['rendererClassName']);
		}
		if (isset($options['renderingOptions'])) {
			foreach ($options['renderingOptions'] as $key => $value) {
				$this->setRenderingOption($key, $value);
			}
		}
		if (isset($options['finishers'])) {
			foreach ($options['finishers'] as $finisherConfiguration) {
				$this->createFinisher($finisherConfiguration['identifier'], isset($finisherConfiguration['options']) ? $finisherConfiguration['options'] : array());
			}
		}

		\TYPO3\Form\Utility\Arrays::assertAllArrayKeysAreValid($options, array('rendererClassName', 'renderingOptions', 'finishers'));
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
	 * @return \TYPO3\Form\Core\Model\Page the newly created page
	 * @throws \TYPO3\Form\Exception\TypeDefinitionNotFoundException
	 * @api
	 */
	public function createPage($identifier, $typeName = 'TYPO3.Form:Page') {
		$typeDefinition = $this->formFieldTypeManager->getMergedTypeDefinition($typeName);

		if (!isset($typeDefinition['implementationClassName'])) {
			throw new \TYPO3\Form\Exception\TypeDefinitionNotFoundException(sprintf('The "implementationClassName" was not set in type definition "%s".', $typeName), 1325689855);
		}
		$implementationClassName = $typeDefinition['implementationClassName'];
		$page = new $implementationClassName($identifier, $typeName);

		if (isset($typeDefinition['label'])) {
			$page->setLabel($typeDefinition['label']);
		}

		if (isset($typeDefinition['rendererClassName'])) {
			$page->setRendererClassName($typeDefinition['rendererClassName']);
		}

		if (isset($typeDefinition['renderingOptions'])) {
			foreach ($typeDefinition['renderingOptions'] as $key => $value) {
				$page->setRenderingOption($key, $value);
			}
		}

		\TYPO3\Form\Utility\Arrays::assertAllArrayKeysAreValid($typeDefinition, array('implementationClassName', 'label', 'rendererClassName', 'renderingOptions'));

		$this->addPage($page);
		return $page;
	}

	/**
	 * Add a new page at the end of the form.
	 *
	 * Instead of this method, you should often use {@link createPage} instead.
	 *
	 * @param Page $page
	 * @return void
	 * @throws \TYPO3\Form\Exception\FormDefinitionConsistencyException if Page is already added to a FormDefinition
	 * @see createPage
	 * @api
	 */
	public function addPage(Page $page) {
		$this->addRenderable($page);
	}

	/**
	 * Get the Form's pages
	 *
	 * @return array<TYPO3\Form\Core\Model\Page> The Form's pages in the correct order
	 * @api
	 */
	public function getPages() {
		return $this->renderables;
	}

	/**
	 * Check whether a page with the given $index exists
	 *
	 * @param integer $index
	 * @return boolean TRUE if a page with the given $index exists, otherwise FALSE
	 * @api
	 */
	public function hasPageWithIndex($index) {
		return isset($this->renderables[$index]);
	}

	/**
	 * Get the page with the passed index. The first page has index zero.
	 *
	 * If page at $index does not exist, an exception is thrown. @see hasPageWithIndex()
	 *
	 * @param integer $index
	 * @return Page the page, or NULL if none found.
	 * @throws \TYPO3\Form\Exception if the specified index does not exist
	 * @api
	 */
	public function getPageByIndex($index) {
		if (!$this->hasPageWithIndex($index)) {
			throw new \TYPO3\Form\Exception(sprintf('There is no page with an index of %d', $index), 1329233627);
		}
		return $this->renderables[$index];
	}

	/**
	 * Adds the specified finisher to this form
	 *
	 * @param \TYPO3\Form\Core\Model\FinisherInterface $finisher
	 * @return void
	 * @api
	 */
	public function addFinisher(FinisherInterface $finisher) {
		$this->finishers[] = $finisher;
	}

	/**
	 * @param string $finisherIdentifier identifier of the finisher as registered in the current form preset (for example: "TYPO3.Form:Redirect")
	 * @param array $options options for this finisher in the format array('option1' => 'value1', 'option2' => 'value2', ...)
	 * @return FinisherInterface
	 * @throws \TYPO3\Form\Exception\FinisherPresetNotFoundException
	 * @api
	 */
	public function createFinisher($finisherIdentifier, array $options = array()) {
		if (isset($this->finisherPresets[$finisherIdentifier]) && is_array($this->finisherPresets[$finisherIdentifier]) && isset($this->finisherPresets[$finisherIdentifier]['implementationClassName'])) {
			$implementationClassName = $this->finisherPresets[$finisherIdentifier]['implementationClassName'];
			$defaultOptions = isset($this->finisherPresets[$finisherIdentifier]['options']) ? $this->finisherPresets[$finisherIdentifier]['options'] : array();

			$options = \TYPO3\Flow\Utility\Arrays::arrayMergeRecursiveOverrule($defaultOptions, $options);

			$finisher = new $implementationClassName;
			$finisher->setOptions($options);
			$this->addFinisher($finisher);
			return $finisher;
		} else {
			throw new \TYPO3\Form\Exception\FinisherPresetNotFoundException('The finisher preset identified by "' . $finisherIdentifier . '" could not be found, or the implementationClassName was not specified.', 1328709784);
		}
	}

	/**
	 * Gets all finishers of this form
	 *
	 * @return array<\TYPO3\Form\Core\Model\FinisherInterface>
	 * @api
	 */
	public function getFinishers() {
		return $this->finishers;
	}

	/**
	 * Add an element to the ElementsByIdentifier Cache.
	 *
	 * @param Renderable\RenderableInterface $renderable
	 * @return void
	 * @throws \TYPO3\Form\Exception\DuplicateFormElementException
	 * @internal
	 */
	public function registerRenderable(Renderable\RenderableInterface $renderable) {
		if ($renderable instanceof FormElementInterface) {
			if (isset($this->elementsByIdentifier[$renderable->getIdentifier()])) {
				throw new \TYPO3\Form\Exception\DuplicateFormElementException(sprintf('A form element with identifier "%s" is already part of the form.', $renderable->getIdentifier()), 1325663761);
			}
			$this->elementsByIdentifier[$renderable->getIdentifier()] = $renderable;
		}
	}

	/**
	 * Remove an element from the ElementsByIdentifier cache
	 *
	 * @param Renderable\RenderableInterface $renderable
	 * @return void
	 * @internal
	 */
	public function unregisterRenderable(Renderable\RenderableInterface $renderable) {
		if ($renderable instanceof FormElementInterface) {
			unset($this->elementsByIdentifier[$renderable->getIdentifier()]);
		}
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
	 * Sets the default value of a form element
	 *
	 * @param string $elementIdentifier identifier of the form element. This supports property paths!
	 * @param mixed $defaultValue
	 * @return void
	 * @internal
	 */
	public function addElementDefaultValue($elementIdentifier, $defaultValue) {
		$this->elementDefaultValues = \TYPO3\Flow\Utility\Arrays::setValueByPath($this->elementDefaultValues, $elementIdentifier, $defaultValue);
	}

	/**
	 * returns the default value of the specified form element
	 * or NULL if no default value was set
	 *
	 * @param string $elementIdentifier identifier of the form element. This supports property paths!
	 * @return mixed The elements default value
	 * @internal
	 */
	public function getElementDefaultValueByIdentifier($elementIdentifier) {
		return \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($this->elementDefaultValues, $elementIdentifier);
	}

	/**
	 * Move $pageToMove before $referencePage
	 *
	 * @param Page $pageToMove
	 * @param Page $referencePage
	 * @return void
	 * @api
	 */
	public function movePageBefore(Page $pageToMove, Page $referencePage) {
		$this->moveRenderableBefore($pageToMove, $referencePage);
	}

	/**
	 * Move $pageToMove after $referencePage
	 *
	 * @param Page $pageToMove
	 * @param Page $referencePage
	 * @return void
	 * @api
	 */
	public function movePageAfter(Page $pageToMove, Page $referencePage) {
		$this->moveRenderableAfter($pageToMove, $referencePage);
	}

	/**
	 * Remove $pageToRemove from form
	 *
	 * @param Page $pageToRemove
	 * @return void
	 * @api
	 */
	public function removePage(Page $pageToRemove) {
		$this->removeRenderable($pageToRemove);
	}

	/**
	 * Bind the current request & response to this form instance, effectively creating
	 * a new "instance" of the Form.
	 *
	 * @param \TYPO3\Flow\Mvc\ActionRequest $request
	 * @param \TYPO3\Flow\Http\Response $response
	 * @return \TYPO3\Form\Core\Runtime\FormRuntime
	 * @api
	 */
	public function bind(\TYPO3\Flow\Mvc\ActionRequest $request, \TYPO3\Flow\Http\Response $response) {
		return new \TYPO3\Form\Core\Runtime\FormRuntime($this, $request, $response);
	}

	/**
	 * @param string $propertyPath
	 * @return ProcessingRule
	 * @api
	 */
	public function getProcessingRule($propertyPath) {
		if (!isset($this->processingRules[$propertyPath])) {
			$this->processingRules[$propertyPath] = new ProcessingRule();
		}
		return $this->processingRules[$propertyPath];
	}

	/**
	 * Get all mapping rules
	 *
	 * @return array<MappingRule>
	 * @internal
	 */
	public function getProcessingRules() {
		return $this->processingRules;
	}

	/**
	 * @return \TYPO3\Form\Utility\SupertypeResolver
	 * @internal
	 */
	public function getFormFieldTypeManager() {
		return $this->formFieldTypeManager;
	}

	/**
	 * @return array
	 * @internal
	 */
	public function getValidatorPresets() {
		return $this->validatorPresets;
	}
}
