<?php
namespace TYPO3\Form\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

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
 *	 <a href="{f:uri.resource(resource: theResource)}">Link to resource</a>
 * </c:form.uploadedResource>
 * </code>
 * <output>
 * <a href="...">Link to resource</a>
 * </output>
 */
class UploadedResourceViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {

	/**
	 * @var TYPO3\FLOW3\Property\PropertyMapper
	 * @FLOW3\Inject
	 */
	protected $propertyMapper;

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
	}

	/**
	 * @param string $as
	 * @return string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function render($as = 'resource') {
		if ($this->hasMappingErrorOccured()) {
			$value = $this->getLastSubmittedFormData();
		} else {
			$value = $this->getValue();
		}

		$resourceObject = NULL;
		if ($value) {
			$resourceObject = $this->propertyMapper->convert($value, 'TYPO3\FLOW3\Resource\Resource');
			if (!$resourceObject instanceof \TYPO3\FLOW3\Resource\Resource) {
				$resourceObject = NULL;
			}
		}

		$this->templateVariableContainer->add($as, $resourceObject);
		$output = $this->renderChildren();
		$this->templateVariableContainer->remove($as);

		return $output;
	}
}

?>
