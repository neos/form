<?php
namespace TYPO3\Flow\Core\Migrations;

/*
 * This file is part of the TYPO3.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Configuration\ConfigurationManager;

/**
 * Adjust "Settings.yaml" to use validationErrorTranslationPackage instead of translationPackage
 */
class Version20160601101500 extends AbstractMigration
{

    /**
     * @return void
     */
    public function up()
    {
        $this->processConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            function (array &$configuration) {
                $presetsConfiguration = \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($configuration, 'TYPO3.Form.presets');
                if (!is_array($presetsConfiguration)) {
                    return;
                }

                $presetsConfiguration = $this->renameTranslationPackage($presetsConfiguration);

                $configuration['TYPO3']['Form']['presets'] = $presetsConfiguration;
            },
            true
        );
    }

    /**
     * Recurse into the given preset and rename translationPackage to validationErrorTranslationPackage
     *
     * @param array $preset
     * @return array
     */
    public function renameTranslationPackage(array &$preset)
    {
        foreach ($preset as $key => $value) {
            if (is_array($value)) {
                if (isset($value['renderingOptions']['translationPackage'])) {
                    $value['renderingOptions']['validationErrorTranslationPackage'] = $value['renderingOptions']['translationPackage'];
                    unset($value['renderingOptions']['translationPackage']);
                }
                $preset[$key] = $this->renameTranslationPackage($value);
            }
        }

        return $preset;
    }
}
