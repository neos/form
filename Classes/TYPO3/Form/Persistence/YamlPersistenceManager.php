<?php
namespace TYPO3\Form\Persistence;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;

/**
 * persistence identifier is some resource:// uri probably
 *
 * @Flow\Scope("singleton")
 */
class YamlPersistenceManager implements FormPersistenceManagerInterface
{
    /**
     * @var string
     */
    protected $savePath;

    /**
     * @param array $settings
     */
    public function injectSettings(array $settings)
    {
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
    public function load($persistenceIdentifier)
    {
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
    public function save($persistenceIdentifier, array $formDefinition)
    {
        $formPathAndFilename = $this->getFormPathAndFilename($persistenceIdentifier);
        file_put_contents($formPathAndFilename, \Symfony\Component\Yaml\Yaml::dump($formDefinition, 99));
    }

    /**
     * Check whether a form with the specified $persistenceIdentifier exists
     *
     * @param string $persistenceIdentifier
     * @return boolean TRUE if a form with the given $persistenceIdentifier can be loaded, otherwise FALSE
     */
    public function exists($persistenceIdentifier)
    {
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
    public function listForms()
    {
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
    protected function getFormPathAndFilename($persistenceIdentifier)
    {
        $formFileName = sprintf('%s.yaml', $persistenceIdentifier);
        return \TYPO3\Flow\Utility\Files::concatenatePaths(array($this->savePath, $formFileName));
    }
}
