<?php
namespace TYPO3\Form\Persistence;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Note: PersistenceIdentifier can be a file name, or anything else depending on the
 * currently active Form Persistence Mananger
 */
interface FormPersistenceManagerInterface {

	/**
	 * Load the array form representation identified by $persistenceIdentifier, and return it
	 *
	 * @param string $persistenceIdentifier
	 * @param boolean $enableAccessChecks if FALSE, no permission checks should be performed.
	 * @return array
	 */
	public function load($persistenceIdentifier, $enableAccessChecks = TRUE);

	/**
	 * Save the array form representation identified by $persistenceIdentifier
	 *
	 * @param string $persistenceIdentifier
	 * @param array $formDefinition
	 */
	public function save($persistenceIdentifier, array $formDefinition);

	/**
	 * List all form definitions which can be loaded through this form persistence
	 * manager.
	 *
	 * Returns an associative array, where the key is the $persistenceIdentifier
	 * and the label is the human-readable name of the form.
	 *
	 * @return array
	 */
	public function listForms();
}
?>