<?php
namespace Neos\Form\Persistence;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * Note: PersistenceIdentifier can be a file name, or anything else depending on the
 * currently active Form Persistence Mananger
 */
interface FormPersistenceManagerInterface
{
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
