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
	 * @return array
	 */
	public function load($persistenceIdentifier);

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
	 * Returns an associative array with each item containing the keys 'name' (the human-readable name of the form)
	 * and 'persistenceIdentifier' (the unique identifier for the Form Persistence Manager e.g. the path to the saved form definition).
	 *
	 * @return array in the format array(array('name' => 'Form 01', 'persistenceIdentifier' => 'path1'), array( .... ))
	 */
	public function listForms();
}
?>