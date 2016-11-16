<?php
namespace TYPO3\Form\Factory;

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
 * Base class for custom *Form Factories*. A Form Factory is responsible for building
 * a {@link TYPO3\Form\Core\Model\FormDefinition}.
 *
 * **This class is meant to be subclassed by developers.**
 *
 * {@inheritDoc}
 *
 * This class implements *Preset Handling* from the Package's settings,
 * making it possible to easily create different presets, only specifying
 * the differences between the presets.
 *
 * Example
 * =======
 *
 * Generally, you should use this class as follows:
 *
 * <pre>
 * class MyFooBarFactory extends AbstractFormFactory {
 *   public function build(array $configuration, $presetName) {
 *     $formDefaults = $this->getPresetConfiguration($presetName);
 *     $formDefinition = new \TYPO3\Form\Core\Model\FormDefinition('nameOfMyForm', $formDefaults);
 *
 *     // now, you should call methods on $formDefinition to add pages and form elements
 *
 *     return $formDefinition;
 *   }
 * }
 * </pre>
 *
 * What Is A Preset?
 * =================
 *
 * A preset is identified by a *preset name* like *Default* or *SimpleHTML*, and
 * consists of configuration. Most importantly, it contains the form element type
 * definition.
 *
 * The AbstractFormFactory loads the presets from the package settings, from the
 * YAML key *TYPO3: Form: presets: [presetName]*.
 *
 * The YAML preset definition has the following structure:
 *
 * <pre>
 * TYPO3:
 *   Form:
 *     presets:
 *       default:
 *         title: 'Default Preset'
 *         formElementTypes:
 *           # ... definition of form element types,
 *           #     see {@link TYPO3\Form\Core\Model\FormDefinition}
 *           #     for the internal structure inside here
 *         finisherTypes:
 *           # definition of the available finishers inside this preset
 *
 *       simpleHtml:
 *         parentPreset: 'default'
 *         title: 'Simple HTML Preset'
 *         # here follows configuration specific to SimpleHtml
 * </pre>
 *
 * In the above example, two presets are defined: The *default* preset and
 * the *simpleHtml* Preset.
 *
 * Preset Hierarchy
 * ================
 *
 * Each preset can have a *parentPreset*, so you can structure the presets hierarchically.
 * In the above example, *simpleHtml* has the parent preset *default*, thus it only needs
 * to specify the *modifications* to the parent preset.
 *
 * **HINT: The "default" preset is already part of this package, so we suggest
 * that you extend this preset to create your own adjustments. This saves you
 * a lot of configuration**
 *
 * Resolving the Preset Hierarchy and merging the configuration is done by the
 * {@link getPresetConfiguration()} method.
 *
 * @api
 */
abstract class AbstractFormFactory implements FormFactoryInterface
{
    /**
     * The settings of the TYPO3.Form package
     *
     * @var array
     * @api
     */
    protected $formSettings;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Configuration\ConfigurationManager
     * @internal
     */
    protected $configurationManager;

    /**
     * @internal
     */
    public function initializeObject()
    {
        $this->formSettings = $this->configurationManager->getConfiguration(\TYPO3\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'TYPO3.Form');
    }

    /**
     * Get the preset configuration by $presetName, taking the preset hierarchy
     * (specified by *parentPreset*) into account.
     *
     * @param string $presetName name of the preset to get the configuration for
     * @return array the preset configuration
     * @throws \TYPO3\Form\Exception\PresetNotFoundException if preset with the name $presetName was not found
     * @api
     */
    public function getPresetConfiguration($presetName)
    {
        if (!isset($this->formSettings['presets'][$presetName])) {
            throw new \TYPO3\Form\Exception\PresetNotFoundException(sprintf('The Preset "%s" was not found underneath TYPO3: Form: presets.', $presetName), 1325685498);
        }
        $preset = $this->formSettings['presets'][$presetName];
        if (isset($preset['parentPreset'])) {
            $parentPreset = $this->getPresetConfiguration($preset['parentPreset']);
            unset($preset['parentPreset']);
            $preset = \TYPO3\Flow\Utility\Arrays::arrayMergeRecursiveOverrule($parentPreset, $preset);
        }
        return $preset;
    }

    /**
     * Helper to be called by every AbstractFormFactory after everything has been built to trigger the "onBuildingFinished"
     * template method on all form elements.
     *
     * @param \TYPO3\Form\Core\Model\FormDefinition $form
     * @return void
     * @api
     */
    protected function triggerFormBuildingFinished(\TYPO3\Form\Core\Model\FormDefinition $form)
    {
        foreach ($form->getRenderablesRecursively() as $renderable) {
            $renderable->onBuildingFinished();
        }
    }

    /**
     * Get the available preset names
     *
     * @return array
     */
    public function getPresetNames()
    {
        return array_keys($this->formSettings['presets']);
    }
}
