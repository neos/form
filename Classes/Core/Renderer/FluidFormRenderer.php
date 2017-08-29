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

use Neos\FluidAdaptor\Core\ViewHelper\TemplateVariableContainer;
use Neos\FluidAdaptor\View\Exception\InvalidTemplateResourceException;
use Neos\FluidAdaptor\View\TemplatePaths;
use Neos\FluidAdaptor\View\TemplateView;
use Neos\Form\Core\Model\Renderable\RootRenderableInterface;
use Neos\Form\Core\Runtime\FormRuntime;
use Neos\Form\Exception;
use Neos\Form\Exception\RenderingException;

/**
 * Default form renderer which used *Fluid Templates* to render *Renderables*.
 *
 * **This class is not intended to be subclassed by developers.**
 *
 * The Fluid Form Renderer is especially capable of rendering nested renderables
 * as well, i.e a form with a page, with all FormElements.
 *
 * Options
 * =======
 *
 * The FluidFormRenderer uses some rendering options which are of particular
 * importance, as they determine how the form field is resolved to a path
 * in the file system.
 *
 * All rendering options are retrieved from the renderable which shall be rendered,
 * using the {@link \Neos\Form\Core\Model\Renderable\RenderableInterface::getRenderingOptions()}
 * method.
 *
 * templatePathPattern
 * -------------------
 *
 * File Path which is used to look up the template. Can contain the placeholders
 * *{@package}* and *{@type}*, which are filled from the *type* of the Renderable.
 *
 * Examples of template path patterns:
 *
 * - *resource://Neos.Form/Private/Templates/MyTemplate.html* <br />
 *   Path without any placeholders; is directly used as template.
 * - *resource://{@package}/Privat/Templates/Form/{@type}.html* <br />
 *   If the current renderable has the namespaced type *Neos.Form:FooBar*,
 *   then this path is *{@package}* from above is replaced with *Neos.Form*
 *   and *{@type}* is replaced with *FooBar*.
 *
 * The use of Template Path Patterns together with Form Element Inheritance
 * is a very powerful way to configure the mapping from Form Element Types
 * to Fluid Templates.
 *
 * layoutPathPattern
 * -----------------
 *
 * This pattern is used to resolve the *layout* which is referenced inside a
 * template. The same constraints as above apply, again *{@package}* and *{@type}*
 * are replaced.
 *
 * renderableNameInTemplate
 * ------------------------
 *
 * This is a mostly-internal setting which controls the name under which the current
 * renderable is made available inside the template. For example, it controls that
 * inside the template of a "Page", the Page object is available using the variable
 * *page*.
 *
 * Rendering Child Renderables
 * ===========================
 *
 * If a renderable wants to render child renderables, inside its template,
 * it can do that using the <code><form:renderable></code> ViewHelper.
 *
 * A template example from Page shall demonstrate this:
 *
 * <pre>
 * {namespace form=Neos\Form\ViewHelpers}
 * <f:for each="{page.elements}" as="element">
 *   <form:renderRenderable renderable="{element}" />
 * </f:for>
 * </pre>
 *
 * Rendering PHP Based Child Renderables
 * =====================================
 *
 * If a child renderable has a *rendererClassName* set (i.e. {@link \Neos\Form\Core\Model\FormElementInterface::getRendererClassName()}
 * returns a non-NULL string), this renderer is automatically instanciated
 * and the rendering for this element is delegated to this Renderer.
 */
class FluidFormRenderer extends TemplateView implements RendererInterface
{
    /**
     * @var FormRuntime
     */
    protected $formRuntime;

    /**
     * @var \TYPO3Fluid\Fluid\Core\Parser\TemplateParser
     */
    protected $templateParser;

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->templateParser = $this->baseRenderingContext->getTemplateParser();
    }

    /**
     * @param FormRuntime $formRuntime
     * @return void
     * @api
     */
    public function setFormRuntime(FormRuntime $formRuntime)
    {
        $this->formRuntime = $formRuntime;
    }

    /**
     * @return FormRuntime
     * @api
     */
    public function getFormRuntime()
    {
        return $this->formRuntime;
    }

    /**
     * Render the passed $renderable and return the rendered Renderable.
     *
     * @param RootRenderableInterface $renderable
     * @return string the rendered $renderable
     * @throws RenderingException
     * @api
     */
    public function renderRenderable(RootRenderableInterface $renderable)
    {
        $renderable->beforeRendering($this->formRuntime);

        $renderableType = $renderable->getType();

        if ($renderable->getRendererClassName() !== null && $renderable->getRendererClassName() !== get_class($this)) {
            $rendererClassName = $renderable->getRendererClassName();
            $renderer = new $rendererClassName;
            if (!($renderer instanceof RendererInterface)) {
                throw new RenderingException(sprintf('The renderer class "%s" for "%s" does not implement RendererInterface.', $rendererClassName, $renderableType), 1326098022);
            }
            $renderer->setControllerContext($this->controllerContext);
            $renderer->setFormRuntime($this->formRuntime);
            return $renderer->renderRenderable($renderable);
        }

        $renderingOptions = $renderable->getRenderingOptions();

        $renderablePathAndFilename = $this->getPathAndFilenameForRenderable($renderableType, $renderingOptions);
        $parsedRenderable = $this->getParsedRenderable($renderable->getType(), $renderablePathAndFilename);
        $currentTemplatePathsResolver = $this->getTemplatePaths();
        $newTemplatePathsResolver = $this->createTemplatePathsResolverWithRenderingOptions($renderingOptions);
        $this->baseRenderingContext->setTemplatePaths($newTemplatePathsResolver);

        if ($this->getCurrentRenderingContext() === null) {
            // We do not have a "current" rendering context yet, so we use the base rendering context
            $this->baseRenderingContext->setControllerContext($this->controllerContext);
            $renderingContext = $this->baseRenderingContext;
        } else {
            $renderingContext = clone $this->getCurrentRenderingContext();
        }
        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(FluidFormRenderer::class, 'currentRenderable', $renderable);

        if (!isset($renderingOptions['renderableNameInTemplate'])) {
            throw new RenderingException(sprintf('The Renderable "%s" did not have the rendering option "renderableNameInTemplate" defined.', $renderableType), 1326094948);
        }

        $templateVariableContainer = new TemplateVariableContainer(array($renderingOptions['renderableNameInTemplate'] => $renderable));
        $renderingContext->setVariableProvider($templateVariableContainer);

        if ($parsedRenderable->hasLayout()) {
            $renderableLayoutName = $parsedRenderable->getLayoutName($renderingContext);
            $renderableLayoutPathAndFilename = $this->getPathAndFilenameForRenderableLayout($renderableLayoutName, $renderingOptions);
            $parsedLayout = $this->getParsedRenderable($renderableLayoutName, $renderableLayoutPathAndFilename);

            $this->startRendering(self::RENDERING_LAYOUT, $parsedRenderable, $renderingContext);
            $output = $parsedLayout->render($renderingContext);
            $this->stopRendering();
        } else {
            $this->startRendering(self::RENDERING_TEMPLATE, $parsedRenderable, $renderingContext);
            $output = $parsedRenderable->render($renderingContext);
            $this->stopRendering();
        }

        $this->baseRenderingContext->setTemplatePaths($currentTemplatePathsResolver);
        $output = $this->formRuntime->invokeRenderCallbacks($output, $renderable);
        return $output;
    }

    public function renderPartial($partialName, $sectionName, array $variables, $ignoreUnknown = false)
    {
        return parent::renderPartial($partialName, $sectionName, $variables, $ignoreUnknown); // TODO: Change the autogenerated stub
    }

    /**
     * @param array $renderingOptions
     * @return TemplatePaths
     */
    protected function createTemplatePathsResolverWithRenderingOptions($renderingOptions)
    {
        /** @var TemplatePaths $newTemplatePathsResolver */
        $newTemplatePathsResolver = clone $this->getTemplatePaths();

        if (isset($renderingOptions['templatePathPattern'])) {
            $pathPattern = str_replace('{@type}', '@action', $renderingOptions['templatePathPattern']);
            $pathPattern = preg_replace('/{(@[a-zA-z0-9_-]*)}/', '$1', $pathPattern);
            $newTemplatePathsResolver->setOption('templatePathAndFilenamePattern', $pathPattern);
        }

        if (isset($renderingOptions['partialPathPattern'])) {
            $pathPattern = str_replace('{@type}', '@partial', $renderingOptions['partialPathPattern']);
            $pathPattern = preg_replace('/{(@[a-zA-z0-9_-]*)}/', '$1', $pathPattern);
            $newTemplatePathsResolver->setOption('partialPathAndFilenamePattern', $pathPattern);
        }

        if (isset($renderingOptions['layoutPathPattern'])) {
            $pathPattern = str_replace('{@type}', '@action', $renderingOptions['layoutPathPattern']);
            $pathPattern = preg_replace('/{(@[a-zA-z0-9_-]*)}/', '$1', $pathPattern);
            $newTemplatePathsResolver->setOption('layoutPathAndFilenamePattern', $pathPattern);
        }

        return $newTemplatePathsResolver;
    }

    /**
     * Get full template path and filename for the given $renderableType.
     *
     * Reads the $renderingOptions['templatePathPattern'], replacing {@package} and {@type}
     * from the given $renderableType.
     *
     * @param string $renderableType
     * @param array $renderingOptions
     * @return string the full path to the template which shall be used.
     * @throws RenderingException
     * @internal
     */
    protected function getPathAndFilenameForRenderable($renderableType, array $renderingOptions)
    {
        if (!isset($renderingOptions['templatePathPattern'])) {
            throw new RenderingException(sprintf('The Renderable "%s" did not have the rendering option "templatePathPattern" defined.', $renderableType), 1326094041);
        }
        list($packageKey, $shortRenderableType) = explode(':', $renderableType);

        return strtr($renderingOptions['templatePathPattern'], array(
            '{@package}' => $packageKey,
            '{@type}' => $shortRenderableType
        ));
    }

    /**
     * Get full layout path and filename for the given $renderableType.
     *
     * Reads the $renderingOptions['layoutPathPattern'], replacing {@package} and {@type}
     * from the given $renderableType.
     *
     * @param string $renderableType
     * @param array $renderingOptions
     * @return string the full path to the layout which shall be used.
     * @throws RenderingException
     * @internal
     */
    protected function getPathAndFilenameForRenderableLayout($renderableType, array $renderingOptions)
    {
        if (!isset($renderingOptions['layoutPathPattern'])) {
            throw new RenderingException(sprintf('The Renderable "%s" did not have the rendering option "layoutPathPattern" defined.', $renderableType), 1326094161);
        }
        list($packageKey, $shortRenderableType) = explode(':', $renderableType);

        return strtr($renderingOptions['layoutPathPattern'], array(
            '{@package}' => $packageKey,
            '{@type}' => $shortRenderableType
        ));
    }

    /**
     * Resolve the partial path and filename based on $this->partialPathAndFilenamePattern.
     *
     * @param string $renderableType The name of the partial
     * @return string the full path which should be used. The path definitely exists.
     * @throws InvalidTemplateResourceException
     * @throws RenderingException
     */
    protected function getPartialPathAndFilename($renderableType)
    {
        $renderingContext = $this->getCurrentRenderingContext();
        $currentRenderable = $renderingContext->getViewHelperVariableContainer()->get(FluidFormRenderer::class, 'currentRenderable');
        $renderingOptions = $currentRenderable->getRenderingOptions();
        if (!isset($renderingOptions['partialPathPattern'])) {
            throw new RenderingException(sprintf('The Renderable "%s" did not have the rendering option "partialPathPattern" defined.', $renderableType), 1326713352);
        }
        list($packageKey, $shortRenderableType) = explode(':', $renderableType);

        $partialPath = strtr($renderingOptions['partialPathPattern'], array(
            '{@package}' => $packageKey,
            '{@type}' => $shortRenderableType
        ));
        if (file_exists($partialPath)) {
            return $partialPath;
        }
        throw new InvalidTemplateResourceException('The template file "' . $partialPath . '" could not be loaded.', 1326713418);
    }

    /**
     * Get the parsed renderable for $renderablePathAndFilename.
     *
     * Internally, uses the templateCompiler automatically.
     *
     * @param string $renderableType
     * @param string $renderablePathAndFilename
     * @return \TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface
     * @throws Exception
     * @internal
     */
    protected function getParsedRenderable($renderableType, $renderablePathAndFilename)
    {
        if (!file_exists($renderablePathAndFilename)) {
            throw new Exception(sprintf('The template "%s" does not exist', $renderablePathAndFilename), 1329233920);
        }
        $templateModifiedTimestamp = \filemtime($renderablePathAndFilename);
        $renderableIdentifier = sprintf('renderable_%s_%s', str_replace(array('.', ':'), '_', $renderableType), sha1($renderablePathAndFilename . '|' . $templateModifiedTimestamp));

        $parsedRenderable = $this->baseRenderingContext->getTemplateParser()->getOrParseAndStoreTemplate(
            $renderableIdentifier,
            function () use ($renderablePathAndFilename) {
                return file_get_contents($renderablePathAndFilename);
            }
        );

        return $parsedRenderable;
    }
}
