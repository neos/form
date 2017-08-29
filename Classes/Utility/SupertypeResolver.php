<?php
namespace Neos\Form\Utility;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Form\Exception\TypeDefinitionNotFoundException;

/**
 * Merges configuration based on the "superTypes" property of a so-called "type definition".
 *
 * @internal
 */
class SupertypeResolver
{
    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param array $settings
     * @internal
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     *
     * @param string $type
     * @param boolean $showHiddenProperties if TRUE, the hidden properties are shown as configured in settings "supertypeResolver.hiddenProperties" are shown as well. FALSE by default
     * @return array
     * @throws TypeDefinitionNotFoundException if a type definition was not found
     * @internal
     */
    public function getMergedTypeDefinition($type, $showHiddenProperties = false)
    {
        if (!isset($this->configuration[$type])) {
            throw new TypeDefinitionNotFoundException(sprintf('Type "%s" not found. Probably some configuration is missing.', $type), 1325686909);
        }
        $mergedTypeDefinition = [];
        if (isset($this->configuration[$type]['superTypes'])) {
            foreach ($this->configuration[$type]['superTypes'] as $superTypeName => $enabled) {
                // Skip unset node types
                if ($enabled === false || $enabled === null) {
                    continue;
                }

                // Make this setting backwards compatible with old array schema (deprecated since 2.0)
                if (!is_bool($enabled)) {
                    $superTypeName = $enabled;
                }

                $mergedTypeDefinition = \Neos\Utility\Arrays::arrayMergeRecursiveOverrule($mergedTypeDefinition, $this->getMergedTypeDefinition($superTypeName, $showHiddenProperties));
            }
        }
        $mergedTypeDefinition = \Neos\Utility\Arrays::arrayMergeRecursiveOverrule($mergedTypeDefinition, $this->configuration[$type]);
        unset($mergedTypeDefinition['superTypes']);

        if ($showHiddenProperties === false && isset($this->settings['supertypeResolver']['hiddenProperties']) && is_array($this->settings['supertypeResolver']['hiddenProperties'])) {
            foreach ($this->settings['supertypeResolver']['hiddenProperties'] as $propertyName) {
                unset($mergedTypeDefinition[$propertyName]);
            }
        }

        return $mergedTypeDefinition;
    }

    /**
     * @param boolean $showHiddenProperties  if TRUE, the hidden properties are shown as configured in settings "supertypeResolver.hiddenProperties" are shown as well. FALSE by default
     * @return array associative array of all completely merged type definitions.
     * @internal
     */
    public function getCompleteMergedTypeDefinition($showHiddenProperties = false)
    {
        $configuration = [];
        foreach (array_keys($this->configuration) as $type) {
            $configuration[$type] = $this->getMergedTypeDefinition($type, $showHiddenProperties);
        }
        return $configuration;
    }
}
