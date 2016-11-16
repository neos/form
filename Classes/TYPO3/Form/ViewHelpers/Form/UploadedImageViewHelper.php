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
 * This ViewHelper makes the specified Image object available for its
 * childNodes.
 * In case the form is redisplayed because of validation errors, a previously
 * uploaded image will be correctly used.
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.upload property="image" />
 * <c:form.uploadedImage property="image" as="theImage">
 *   <a href="{f:uri.resource(resource: theImage.resource)}">Link to image resource</a>
 * </c:form.uploadedImage>
 * </code>
 * <output>
 * <a href="...">Link to image resource</a>
 * </output>
 */
class UploadedImageViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper
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
     * @api
     */
    public function render($as = 'image')
    {
        $this->templateVariableContainer->add($as, $this->getUploadedImage());
        $output = $this->renderChildren();
        $this->templateVariableContainer->remove($as);

        return $output;
    }

    /**
     * Returns a previously uploaded image.
     * If errors occurred during property mapping for this property, NULL is returned
     *
     * @return \TYPO3\Media\Domain\Model\Image
     */
    protected function getUploadedImage()
    {
        if ($this->getMappingResultsForProperty()->hasErrors()) {
            return null;
        }
        $image = $this->getValue(false);
        if ($image instanceof \TYPO3\Media\Domain\Model\Image) {
            return $image;
        }
        return $this->propertyMapper->convert($image, \TYPO3\Media\Domain\Model\Image::class);
    }
}
