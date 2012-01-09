<?php
namespace TYPO3\Form\Domain\Factory;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * A Form Factory is responsible for building a {@link TYPO3\Form\Domain\Model\FormDefinition}.
 * **Instead of implementing this interface, subclassing {@link AbstractFormFactory} is more appropriate
 * in most cases**.
 *
 * A Form Factory can be called anytime a FormDefinition should be built; in most cases
 * it is done through an invocation of a Form Rendering ViewHelper.
 *
 * @api
 */
interface FormFactoryInterface {

	/**
	 * Build a form definition, depending on some configuration and a "Preset Name".
	 *
	 * The configuration array is factory-specific; for example a YAML or JSON factory
	 * could retrieve the path to the YAML / JSON file via the configuration array.
	 *
	 * The $presetName is intended to facilitate the generation of the same form in
	 * different *Representation Formats* (f.e. simple HTML, HTML with JavaScript enabled, ...).
	 *
	 * The {@link AbstractFormFactory} provides implementations which handle the
	 * $presetName correctly; so you are advised to sublclass AbstractFormFactory
	 * directly.
	 *
	 * @param array $configuration factory-specific configuration array
	 * @param string $presetName The name of the "Form Preset" to use; it is factory-specific to implement this.
	 * @return \TYPO3\Form\Domain\Model\FormDefinition a newly built form definition
	 * @api
	 */
	public function build(array $configuration, $presetName);
}
?>