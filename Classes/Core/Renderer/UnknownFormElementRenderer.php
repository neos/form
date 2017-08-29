<?php
namespace Neos\Form\Core\Renderer;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Form\Core\Model\Renderable\RenderableInterface;
use Neos\Form\Core\Model\Renderable\RootRenderableInterface;
use Neos\Form\Exception\TypeDefinitionNotFoundException;

/**
 * Renderer for unknown Form Elements
 * This is used to render Form Elements without definition depending on the context:
 * In "preview mode" (e.g. inside the FormBuilder) a div with an error message is rendered
 * If previewMode is FALSE this will return an empty string if the rendering Option "skipUnknownElements" is TRUE for
 * the form, or throw an Exception otherwise.
 */
class UnknownFormElementRenderer extends AbstractElementRenderer
{
    /**
     * This renders the given $renderable depending on the context:
     * In preview Mode this returns an error message. Otherwise this throws an exception or returns an empty string
     * depending on the "skipUnknownElements" rendering option
     *
     * @param RootRenderableInterface $renderable
     * @return string the rendered $renderable
     * @throws TypeDefinitionNotFoundException
     * @api
     */
    public function renderRenderable(RootRenderableInterface $renderable)
    {
        $renderingOptions = $this->formRuntime->getRenderingOptions();
        $previewMode = isset($renderingOptions['previewMode']) && $renderingOptions['previewMode'] === true;
        if ($previewMode) {
            return sprintf('<div class="typo3-form-unknown-element" data-element="%s"><em>Unknown Form Element "%s"</em></div>', htmlspecialchars($this->getRenderablePath($renderable)), htmlspecialchars($renderable->getType()));
        }
        $skipUnknownElements = isset($renderingOptions['skipUnknownElements']) && $renderingOptions['skipUnknownElements'] === true;
        if (!$skipUnknownElements) {
            throw new TypeDefinitionNotFoundException(sprintf('Type "%s" not found. Probably some configuration is missing.', $renderable->getType()), 1382364019);
        }
        return '';
    }

    /**
     * Returns the path of a $renderable in the format <formIdentifier>/<sectionIdentifier>/<sectionIdentifier>/.../<elementIdentifier>
     *
     * @param RootRenderableInterface $renderable
     * @return string
     */
    protected function getRenderablePath(RootRenderableInterface $renderable)
    {
        $path = $renderable->getIdentifier();
        while ($renderable instanceof RenderableInterface && $renderable = $renderable->getParentRenderable()) {
            $path = $renderable->getIdentifier() . '/' . $path;
        }
        return $path;
    }
}
