<?php
namespace TYPO3\Form\Persistence;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * persistence identifier is some resource:// uri probably
 *
 * @FLOW3\Scope("singleton")
 */
class YamlPersistenceManager implements FormPersistenceManagerInterface {

	/**
	 * @var array
	 */
	protected $allowedDirectories;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->allowedDirectories = (isset($settings['yamlPersistenceManager']['allowedDirectories']) && is_array($settings['yamlPersistenceManager']['allowedDirectories']) ? $settings['yamlPersistenceManager']['allowedDirectories'] : array());
	}

	public function load($persistenceIdentifier) {
		$persistenceIdentifier = \TYPO3\FLOW3\Utility\Files::getUnixStylePath($persistenceIdentifier);
		if ($this->isDirectoryAllowed($persistenceIdentifier)) {
			return \Symfony\Component\Yaml\Yaml::parse(file_get_contents($persistenceIdentifier));
		} else {
			throw new Exception(sprintf('The form identified by "%s" was not allowed to be loaded.', $persistenceIdentifier), 1328160893);
		}
	}

	public function save($persistenceIdentifier, array $formDefinition) {
		$persistenceIdentifier = \TYPO3\FLOW3\Utility\Files::getUnixStylePath($persistenceIdentifier);
		if ($this->isDirectoryAllowed($persistenceIdentifier)) {
			file_put_contents($persistenceIdentifier, \Symfony\Component\Yaml\Yaml::dump($formDefinition, 99));
		} else {
			throw new Exception(sprintf('The form identified by "%s" was not allowed to be saved.', $persistenceIdentifier), 1328160897);
		}
	}

	public function listForms() {
		$forms = array();

		foreach ($this->allowedDirectories as $directory) {
			$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
			foreach ($iterator as $fileObject) {
				$form = $this->load($fileObject->getPathname());
				$forms[] = array(
					'name' => $form['identifier'],
					'persistenceIdentifier' => $fileObject->getPathname()
				);
			}
		}
		return $forms;
	}

	protected function isDirectoryAllowed($persistenceIdentifier) {
		foreach ($this->allowedDirectories as $directory) {
			if (strpos($persistenceIdentifier, $directory) === 0) {
					// the $persistence identifier starts with $directory
				return TRUE;
			}
		}
		return FALSE;
	}
}
?>