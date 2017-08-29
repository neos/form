<?php
namespace Neos\Form\Core\Model\Renderable;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Form\Core\Runtime\FormRuntime;

/**
 * Base interface which all parts of a form must adhere to.
 *
 * **This interface should not be implemented by developers**, it is only
 * used for improving the internal code structure.
 */
interface RootRenderableInterface
{
    /**
     * Abstract "type" of this Renderable. Is used during the rendering process
     * to determine the template file or the View PHP class being used to render
     * the particular element.
     *
     * @return string
     * @api
     */
    public function getType();

    /**
     * The identifier of this renderable
     *
     * @return string
     * @api
     */
    public function getIdentifier();

    /**
     * Get the label which shall be displayed next to the form element
     *
     * @return string
     * @api
     */
    public function getLabel();

    /**
     * This is a callback that is invoked by the Renderer before the corresponding element is rendered.
     * Use this to access previously submitted values and/or modify the $formRuntime before an element
     * is outputted to the browser.
     *
     * @param FormRuntime $formRuntime
     * @return void
     * @api
     */
    public function beforeRendering(FormRuntime $formRuntime);

    /**
     * Get the renderer class name to be used to display this renderable;
     * must implement RendererInterface
     *
     * Is only set if a specific renderer should be used for this renderable,
     * if it is NULL the caller needs to determine the renderer or take care
     * of the renderer itself.
     *
     * @return string the renderer class name
     * @api
     */
    public function getRendererClassName();

    /**
     * Get all rendering options
     *
     * @return array associative array of rendering options
     * @api
     */
    public function getRenderingOptions();
}
