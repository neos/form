<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A Page, being part of a bigger FormDefinition.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * This class contains multiple FormElements ({@link FormElementInterface}).
 *
 * Please see {@link FormDefinition} for an in-depth explanation.
 */
class Page implements RenderableInterface {

	/**
	 * The identifier
	 *
	 * @var string
	 * @internal
	 */
	protected $identifier;

	/**
	 * The parent form definition
	 *
	 * @var \TYPO3\Form\Domain\Model\FormDefinition
	 * @internal
	 */
	protected $parentForm;

	/**
	 * The elements of this form, numerically indexed
	 *
	 * @var array<TYPO3\Form\Domain\Model\FormElementInterface>
	 * @internal
	 */
	protected $elements = array();

	/**
	 * Position of page in form (0-based)
	 *
	 * @var integer
	 * @internal
	 */
	protected $index = 0;

	protected $label;

	/**
	 * Constructor. Needs this Page's identifier
	 *
	 * @param string $identifier The Page's identifier
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
	 * Get the Page's identifier
	 *
	 * @return string The Page's identifier
	 * @api
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Get the FormDefinition this page belongs to
	 *
	 * @return \TYPO3\Form\Domain\Model\FormDefinition The Page's parent form definition
	 * @internal
	 */
	public function getParentForm() {
		return $this->parentForm;
	}

	/**
	 * Set the FormDefinition this page belongs to
	 *
	 * @param \TYPO3\Form\Domain\Model\FormDefinition $parentForm The Page's parent form definition
	 * @internal
	 */
	public function setParentForm(FormDefinition $parentForm = NULL) {
		$this->parentForm = $parentForm;
	}

	/**
	 * Get the Page's Form Elements
	 *
	 * @return array<\TYPO3\Form\Domain\Model\FormElementInterface> The Page's elements
	 * @api
	 */
	public function getElements() {
		return $this->elements;
	}

	/**
	 * Add a new form element at the end of the page
	 *
	 * @param FormElementInterface $formElement The form element to add
	 * @throws \TYPO3\Form\Exception\FormDefinitionConsistencyException if FormElement is already added to a Page
	 * @api
	 */
	public function addElement(FormElementInterface $formElement) {
		if ($formElement->getParentPage() !== NULL) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException(sprintf('The FormElement with identifier "%s" is already added to another Page (page identifier: "%s").', $formElement->getIdentifier(), $formElement->getParentPage()->getIdentifier()), 1325665144);
		}

		$this->elements[] = $formElement;
		$formElement->setParentPage($this);
		if ($this->parentForm !== NULL) {
			$this->parentForm->addElementToElementsByIdentifierCache($formElement);
		}
	}

	/**
	 *
	 * @param string $identifier
	 * @param string $typeName
	 * @return \TYPO3\Form\Domain\Model\FormElementInterface
	 * @throws \Exception
	 */
	public function createElement($identifier, $typeName) {
		if ($this->parentForm === NULL) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException(sprintf('The page "%s" is not attached to a parent form, thus createElement() cannot be called.', $this->identifier), 1325742259);
		}
		$typeDefinition = $this->parentForm->getFormFieldTypeManager()->getMergedTypeDefinition($typeName);

		if (!isset($typeDefinition['implementationClassName'])) {
			throw new \TYPO3\Form\Exception\TypeDefinitionNotFoundException(sprintf('The "implementationClassName" was not set in type definition "%s".', $typeName), 1325689855);
		}
		$implementationClassName = $typeDefinition['implementationClassName'];
		$element = new $implementationClassName($identifier, $typeName);

		if (isset($typeDefinition['label'])) {
			$element->setLabel($typeDefinition['label']);
		}

		if (isset($typeDefinition['defaultValue'])) {
			$element->setDefaultValue($typeDefinition['defaultValue']);
		}

		if (isset($typeDefinition['properties'])) {
			foreach ($typeDefinition['properties'] as $key => $value) {
				$element->setProperty($key, $value);
			}
		}

		\TYPO3\Form\Utility\Arrays::assertAllArrayKeysAreValid($typeDefinition, array('implementationClassName', 'label', 'defaultValue', 'properties'));

		$this->addElement($element);
		return $element;
	}

	/**
	 * @return string
	 * @todo document
	 */
	public function getType() {
		return 'TYPO3.Form:Page';
	}

	/**
	 * Move FormElement $element before $referenceElement.
	 *
	 * Both $element and $referenceElement must be direct descendants of this $page.
	 *
	 * @param FormElementInterface $elementToMove
	 * @param FormElementInterface $referenceElement
	 * @api
	 */
	public function moveElementBefore(FormElementInterface $elementToMove, FormElementInterface $referenceElement) {
		if ($elementToMove->getParentPage() !== $referenceElement->getParentPage() || $elementToMove->getParentPage() !== $this) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException('Moved elements need to be on the same page.', 1326088365);
		}
		$reorderedElements = array();
		foreach ($this->elements as $element) {
			if ($element === $elementToMove) continue;

			if ($element === $referenceElement) {
				$reorderedElements[] = $elementToMove;
			}
			$reorderedElements[] = $element;
		}
		$this->elements = $reorderedElements;
	}

	/**
	 * Move FormElement $element after $referenceElement
	 *
	 * Both $element and $referenceElement must be direct descendants of this $page.
	 *
	 * @param FormElementInterface $elementToMove
	 * @param FormElementInterface $referenceElement
	 * @api
	 */
	public function moveElementAfter(FormElementInterface $elementToMove, FormElementInterface $referenceElement) {
		if ($elementToMove->getParentPage() !== $referenceElement->getParentPage() || $elementToMove->getParentPage() !== $this) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException('Moved elements need to be on the same page.', 1326088369);
		}
		$reorderedElements = array();
		foreach ($this->elements as $element) {
			if ($element === $elementToMove) continue;

			$reorderedElements[] = $element;
			if ($element === $referenceElement) {
				$reorderedElements[] = $elementToMove;
			}
		}
		$this->elements = $reorderedElements;
	}

	/**
	 * Remove $elementToRemove from Page
	 *
	 * @param FormElementInterface $elementToRemove
	 * @api
	 */
	public function removeElement(FormElementInterface $elementToRemove) {
		if ($elementToRemove->getParentPage() !== $this) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException('The element to be removed must be part of the given page.', 1326088956);
		}

		$updatedElements = array();
		foreach ($this->elements as $element) {
			if ($element === $elementToRemove) continue;

			$updatedElements[] = $element;
		}
		$this->elements = $updatedElements;
		if ($this->parentForm !== NULL) {
			$this->parentForm->removeElementFromElementsByIdentifierCache($elementToRemove);
		}
		$elementToRemove->setParentPage(NULL);
	}

	/**
	 * @return string
	 * @todo document
	 */
	public function getTemplateVariableName() {
		return 'page';
	}

	/**
	 * Get the index of the page inside the form.
	 *
	 * Only meaningful if this page is attached to a FormDefinition.
	 *
	 * @return integer The index of the Page
	 * @api
	 */
	public function getIndex() {
		return $this->index;
	}

	/**
	 * @param integer $index
	 * @internal
	 */
	public function setIndex($index) {
		$this->index = $index;
	}

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}


}
?>