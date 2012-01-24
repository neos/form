<?php
namespace TYPO3\Form\Core\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A base class for "section-like" form parts like "Page" or "Section" (which
 * is rendered as "Fieldset")
 *
 * **This class should not be subclassed by developers**, it is only
 * used for improving the internal code structure.
 *
 * This class contains multiple FormElements ({@link FormElementInterface}).
 *
 * Please see {@link FormDefinition} for an in-depth explanation.
 */
abstract class AbstractSection extends Renderable\AbstractCompositeRenderable {

	/**
	 * Constructor. Needs the identifier and type of this element
	 *
	 * @param string $identifier The Page's identifier
	 * @param string $type The Page's type
	 * @throws \TYPO3\Form\Exception\IdentifierNotValidException if the identifier was no non-empty string
	 * @api
	 */
	public function __construct($identifier, $type) {
		if (!is_string($identifier) || strlen($identifier) === 0) {
			throw new \TYPO3\Form\Exception\IdentifierNotValidException('The given identifier was not a string or the string was empty.', 1325574803);
		}

		$this->identifier = $identifier;
		$this->type = $type;
	}

	/**
	 * Get the child Form Elements
	 *
	 * @return array<\TYPO3\Form\Domain\Model\FormElementInterface> The Page's elements
	 * @api
	 */
	public function getElements() {
		return $this->renderables;
	}

	/**
	 * Get the child Form Elements
	 *
	 * @return array<\TYPO3\Form\Domain\Model\FormElementInterface> The Page's elements
	 * @api
	 */
	public function getElementsRecursively() {
		return $this->getRenderablesRecursively();
	}

	/**
	 * Add a new form element at the end of the section
	 *
	 * @param FormElementInterface $formElement The form element to add
	 * @throws \TYPO3\Form\Exception\FormDefinitionConsistencyException if FormElement is already added to a section
	 * @api
	 */
	public function addElement(FormElementInterface $formElement) {
		$this->addRenderable($formElement);
	}

	/**
	 * Create a form element with the given $identifier and attach it to this section/page.
	 *
	 * - Create Form Element object based on the given $typeName
	 * - set defaults inside the Form Element (based on the parent form's field defaults)
	 * - attach Form Element to this Section/Page
	 * - return the newly created Form Element object
	 *
	 *
	 * @param string $identifier Identifier of the new form element
	 * @param string $typeName type of the new form element
	 * @return \TYPO3\Form\Domain\Model\FormElementInterface the newly created form element
	 * @throws \TYPO3\Form\Exception\TypeDefinitionNotValidException
	 * @throws \TYPO3\Form\Exception\FormDefinitionConsistencyException if this section is not connected to a parent form.
	 * @api
	 */
	public function createElement($identifier, $typeName) {
		$formDefinition = $this->parentRenderable;
		while($formDefinition !== NULL && !($formDefinition instanceof FormDefinition)) {
			$formDefinition = $formDefinition->getParentRenderable();
		}
		if ($formDefinition === NULL) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException(sprintf('The page "%s" is not attached to a parent form, thus createElement() cannot be called.', $this->identifier), 1325742259);
		}

		$typeDefinition = $formDefinition->getFormFieldTypeManager()->getMergedTypeDefinition($typeName);

		if (!isset($typeDefinition['implementationClassName'])) {
			throw new \TYPO3\Form\Exception\TypeDefinitionNotFoundException(sprintf('The "implementationClassName" was not set in type definition "%s".', $typeName), 1325689855);
		}
		$implementationClassName = $typeDefinition['implementationClassName'];
		$element = new $implementationClassName($identifier, $typeName);
		if (!$element instanceof \TYPO3\Form\Core\Model\FormElementInterface) {
			throw new \TYPO3\Form\Exception\TypeDefinitionNotValidException(sprintf('The "implementationClassName" for element "%s" ("%s") does not implement the FormElementInterface.', $identifier, $implementationClassName), 1327318156);
		}
		unset($typeDefinition['implementationClassName']);

		$element->setOptions($typeDefinition);

		$this->addElement($element);
		$element->initializeFormElement();
		return $element;
	}

	/**
	 * Move FormElement $element before $referenceElement.
	 *
	 * Both $element and $referenceElement must be direct descendants of this Section/Page.
	 *
	 * @param FormElementInterface $elementToMove
	 * @param FormElementInterface $referenceElement
	 * @api
	 */
	public function moveElementBefore(FormElementInterface $elementToMove, FormElementInterface $referenceElement) {
		$this->moveRenderableBefore($elementToMove, $referenceElement);
	}

	/**
	 * Move FormElement $element after $referenceElement
	 *
	 * Both $element and $referenceElement must be direct descendants of this Section/Page.
	 *
	 * @param FormElementInterface $elementToMove
	 * @param FormElementInterface $referenceElement
	 * @api
	 */
	public function moveElementAfter(FormElementInterface $elementToMove, FormElementInterface $referenceElement) {
		$this->moveRenderableAfter($elementToMove, $referenceElement);
	}

	/**
	 * Remove $elementToRemove from this Section/Page
	 *
	 * @param FormElementInterface $elementToRemove
	 * @api
	 */
	public function removeElement(FormElementInterface $elementToRemove) {
		$this->removeRenderable($elementToRemove);
	}
}
?>