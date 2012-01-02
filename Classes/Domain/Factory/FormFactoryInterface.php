<?php
namespace TYPO3\Form\Domain\Factory;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * @todo document
 */
interface FormFactoryInterface {
	/**
	 * @param array $configuration The factory-specific configuration
	 * @todo document
	 */
	public function build(array $configuration);
}
?>