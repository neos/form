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
 * persistence identifier is some resource:// uri probably
 *
 * @Flow\Scope("singleton")
 */
class YamlPersistenceManager implements FormPersistenceManagerInterface {

	/**
	 * @var string
	 */
	protected $savePath;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		if (isset($settings['yamlPersistenceManager']['savePath'])) {
			$this->savePath = $settings['yamlPersistenceManager']['savePath'];
			if (!is_dir($this->savePath)) {
				\TYPO3\Flow\Utility\Files::createDirectoryRecursively($this->savePath);
			}
		}
	}

	/**
	 * Load the array form representation identified by $persistenceIdentifier, and return it
	 *
	 * @param string $persistenceIdentifier
	 * @return array
	 * @throws \TYPO3\Form\Exception\PersistenceManagerException
	 */
	public function load($persistenceIdentifier) {
		if (!$this->exists($persistenceIdentifier)) {
			throw new \TYPO3\Form\Exception\PersistenceManagerException(sprintf('The form identified by "%s" could not be loaded in "%s".', $persistenceIdentifier, $this->getFormPathAndFilename($persistenceIdentifier)), 1329307034);
		}
		$formPathAndFilename = $this->getFormPathAndFilename($persistenceIdentifier);
		return \Symfony\Component\Yaml\Yaml::parse(file_get_contents($formPathAndFilename));
	}

	/**
	 * Save the array form representation identified by $persistenceIdentifier
	 *
	 * @param string $persistenceIdentifier
	 * @param array $formDefinition
	 */
	public function save($persistenceIdentifier, array $formDefinition) {
		$formPathAndFilename = $this->getFormPathAndFilename($persistenceIdentifier);
		file_put_contents($formPathAndFilename, \Symfony\Component\Yaml\Yaml::dump($formDefinition, 99));
	}

	/**
	 * Check whether a form with the specified $persistenceIdentifier exists
	 *
	 * @param string $persistenceIdentifier
	 * @return boolean TRUE if a form with the given $persistenceIdentifier can be loaded, otherwise FALSE
	 */
	public function exists($persistenceIdentifier) {
		return is_file($this->getFormPathAndFilename($persistenceIdentifier));
	}

	/**
	 * List all form definitions which can be loaded through this form persistence
	 * manager.
	 *
	 * Returns an associative array with each item containing the keys 'name' (the human-readable name of the form)
	 * and 'persistenceIdentifier' (the unique identifier for the Form Persistence Manager e.g. the path to the saved form definition).
	 *
	 * @return array in the format array(array('name' => 'Form 01', 'persistenceIdentifier' => 'path1'), array( .... ))
	 */
	public function listForms() {
		$forms = array();
		$directoryIterator = new \DirectoryIterator($this->savePath);

		foreach ($directoryIterator as $fileObject) {
			if (!$fileObject->isFile()) {
				continue;
			}
			$fileInfo = pathinfo($fileObject->getFilename());
			if (strtolower($fileInfo['extension']) !== 'yaml') {
				continue;
			}
			$persistenceIdentifier = $fileInfo['filename'];
			$form = $this->load($persistenceIdentifier);
			$forms[] = array(
				'identifier' => $form['identifier'],
				'name' => isset($form['label']) ? $form['label'] : $form['identifier'],
				'persistenceIdentifier' => $persistenceIdentifier
			);
		}
		return $forms;
	}

	/**
	 * Returns the absolute path and filename of the form with the specified $persistenceIdentifier
	 * Note: This (intentionally) does not check whether the file actually exists
	 *
	 * @param string $persistenceIdentifier
	 * @return string the absolute path and filename of the form with the specified $persistenceIdentifier
	 */
	protected function getFormPathAndFilename($persistenceIdentifier) {
		$formFileName = sprintf('%s.yaml', $persistenceIdentifier);
		return \TYPO3\Flow\Utility\Files::concatenatePaths(array($this->savePath, $formFileName));
	}
}
