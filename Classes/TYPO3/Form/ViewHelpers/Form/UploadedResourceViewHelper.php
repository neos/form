<?php
namespace TYPO3\Form\ViewHelpers\Form;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

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
class UploadedResourceViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper
{
    /**
     * @var \TYPO3\Flow\Property\PropertyMapper
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
     * @return \TYPO3\Flow\Resource\Resource
     */
    protected function getUploadedResource()
    {
        if ($this->getMappingResultsForProperty()->hasErrors()) {
            return null;
        }
        $resourceObject = $this->getValue(false);
        if ($resourceObject instanceof \TYPO3\Flow\Resource\Resource) {
            return $resourceObject;
        }
        return $this->propertyMapper->convert($resourceObject, \TYPO3\Flow\Resource\Resource::class);
    }
}
