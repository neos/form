<?php
namespace Neos\Flow\Core\Migrations;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Utility\ObjectAccess;

/**
 * Adjust "Settings.yaml" to use validationErrorTranslationPackage instead of translationPackage
 */
class Version20160601101500 extends AbstractMigration
{

    public function getIdentifier()
    {
        return 'TYPO3.Form-20160601101500';
    }

    /**
     * @return void
     */
    public function up()
    {
        $this->processConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            function (array &$configuration) {
                $presetsConfiguration = ObjectAccess::getPropertyPath($configuration, 'Neos.Form.presets');
                if (!is_array($presetsConfiguration)) {
                    return;
                }

                $presetsConfiguration = $this->renameTranslationPackage($presetsConfiguration);

                $configuration['Neos']['Form']['presets'] = $presetsConfiguration;
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
