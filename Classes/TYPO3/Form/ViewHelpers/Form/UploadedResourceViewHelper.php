<?php
namespace TYPO3\Form\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use Neos\FluidAdaptor\ViewHelpers\Form\AbstractFormFieldViewHelper;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Property\PropertyMapper;
use TYPO3\Flow\Resource\Resource as PersistentResource;

/**
 * This ViewHelper makes the specified Resource object available for its
 * childNodes. If no resource object wsa found at the specified position,
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
     * @author Sebastian Kurfürst <sebastian@typo3.org>
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
    }

    /**
     * @param string $as
     * @return string
     * @author Sebastian Kurfürst <sebastian@typo3.org>
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
     * If errors occurred during property mapping for this property, NULL is returned
     *
     * @return Resource
     */
    protected function getUploadedResource()
    {
        if ($this->getMappingResultsForProperty()->hasErrors()) {
            return null;
        }
        $resourceObject = $this->getValue(false);
        if ($resourceObject instanceof PersistentResource) {
            return $resourceObject;
        }
        return $this->propertyMapper->convert($resourceObject, PersistentResource::class);
    }
}
