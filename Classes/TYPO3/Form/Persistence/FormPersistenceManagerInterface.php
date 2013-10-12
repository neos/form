<?php
namespace TYPO3\Form\Persistence;

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
	 * Check whether a form with the specified $persistenceIdentifier exists
	 *
	 * @param string $persistenceIdentifier
	 * @return boolean TRUE if a form with the given $persistenceIdentifier can be loaded, otherwise FALSE
	 */
	public function exists($persistenceIdentifier);

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
