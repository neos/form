<?php

namespace Neos\Form\ViewHelpers\Form;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\FluidAdaptor\ViewHelpers\Form\AbstractFormFieldViewHelper;

/**
 * This ViewHelper makes the specified PersistentResource available for its
 * childNodes. If no resource object was found at the specified position,
 * the child nodes are not rendered.
 *
 * In case the form is redisplayed because of validation errors, a previously
 * uploaded resource will be correctly used.
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.upload property="file" />
 * <c:form.uploadedResource property="file" as="theResource">
 *   <a href="{f:uri.resource(resource: theResource)}">Link to resource</a>
 * </c:form.uploadedResource>
 * </code>
 * <output>
 * <a href="...">Link to resource</a>
 * </output>
 */
class UploadedResourceViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var PropertyMapper
     * @Flow\Inject
     */
    protected $propertyMapper;

    /**
     * Initialize the arguments.
     *
     * @return void
     *
     * @author Sebastian Kurfürst <sebastian@typo3.org>
     *
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
    }

    /**
     * @param string $as
     *
     * @return string
     *
     * @author Sebastian Kurfürst <sebastian@typo3.org>
     *
     * @api
     */
    public function render($as = 'resource')
    {
        $this->templateVariableContainer->add($as, $this->getUploadedResource());
        $output = $this->renderChildren();
        $this->templateVariableContainer->remove($as);

        return $output;
    }

    /**
     * Returns a previously uploaded resource.
     * If errors occurred during property mapping for this property, NULL is returned.
     *
     * @return PersistentResource
     */
    protected function getUploadedResource()
    {
        if ($this->getMappingResultsForProperty()->hasErrors()) {
            return;
        }
        $resourceObject = $this->getValue(false);
        if ($resourceObject instanceof PersistentResource) {
            return $resourceObject;
        }

        return $this->propertyMapper->convert($resourceObject, PersistentResource::class);
    }
}
