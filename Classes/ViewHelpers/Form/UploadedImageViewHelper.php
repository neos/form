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
use Neos\FluidAdaptor\ViewHelpers\Form\AbstractFormFieldViewHelper;
use Neos\Media\Domain\Model\Image;
use Neos\Media\Domain\Model\ImageInterface;

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
class UploadedImageViewHelper extends AbstractFormFieldViewHelper
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
     * @throws \Neos\FluidAdaptor\Core\ViewHelper\Exception
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('as', 'string', 'Variable name to use for the uploaded image', false, 'image');
    }

    /**
     * @return string
     * @api
     */
    public function render(): string
    {
        $as = $this->arguments['as'];
        $this->templateVariableContainer->add($as, $this->getUploadedImage());
        $output = $this->renderChildren();
        $this->templateVariableContainer->remove($as);

        return $output;
    }

    /**
     * Returns a previously uploaded image.
     * If errors occurred during property mapping for this property, NULL is returned
     *
     * @return ImageInterface
     * @throws \Neos\Flow\Property\Exception
     * @throws \Neos\Flow\Security\Exception
     */
    protected function getUploadedImage(): ?ImageInterface
    {
        if ($this->getMappingResultsForProperty()->hasErrors()) {
            return null;
        }
        $image = $this->getPropertyValue();
        if ($image instanceof ImageInterface) {
            return $image;
        }
        return $this->propertyMapper->convert($this->getValueAttribute(), Image::class);
    }
}
