<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A Page
 */
class Page implements RenderableInterface {

	/**
	 * The identifier
	 * @var string
	 */
	protected $identifier;

	/**
	 * The parent form
	 * @var \TYPO3\Form\Domain\Model\Form
	 */
	protected $parentForm;

	/**
	 * The elements
	 * @var array<TYPO3\Form\Domain\Model\FormElementInterface>
	 */
	protected $elements = array();

	/**
	 * Constructor. Needs this Page's identifier
	 *
	 * @param string $identifier The Page's identifier
	 * @return void
	 * @api
	 */
	public function __construct($identifier) {
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
	 * Get the Page's parent form
	 *
	 * @return \TYPO3\Form\Domain\Model\Form The Page's parent form
	 * @internal
	 */
	public function getParentForm() {
		return $this->parentForm;
	}

	/**
	 * Sets this Page's parent form
	 *
	 * @param \TYPO3\Form\Domain\Model\Form $parentForm The Page's parent form
	 * @return void
	 * @internal
	 */
	public function setParentForm(Form $parentForm) {
		$this->parentForm = $parentForm;
	}

	/**
	 * Get the Page's elements
	 *
	 * @return array<\TYPO3\Form\Domain\Model\FormElementInterface> The Page's elements
	 */
	public function getElements() {
		return $this->elements;
	}


	/**
	 * Add a new form element at the end of the page
	 *
	 * @param FormElementInterface $formElement
	 * @api
	 */
	public function addElement(FormElementInterface $formElement) {
		$this->elements[] = $formElement;
		$formElement->setParentPage($this);
	}

	/**
	 * @todo document
	 * @internal
	 */
	public function render() {
		$renderer = new \TYPO3\Form\Domain\Renderer\FluidRenderer($this);
		$renderer->setRenderableVariableName('page');
		$renderer->setControllerContext($this->getControllerContext());

		// TODO: RendererResolver shall be called HERE
		// TODO: move the "assign" to the RendererResolver lateron
		$renderer->assign('page', $this);
		return $renderer->render();
	}

	public function getControllerContext() {
		return $this->parentForm->getControllerContext();
	}

	public function getType() {
		return 'TYPO3.Form:Page';
	}
}
?>