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

use TYPO3\Flow\Annotations as Flow;

/**
 * Display a jQuery date picker.
 *
 * Note: Requires jQuery UI to be included on the page.
 */
class DatePickerViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    /**
     * @var TYPO3\Flow\Property\PropertyMapper
     * @Flow\Inject
     */
    protected $propertyMapper;

    /**
     * Initialize the arguments.
     *
     * @return void

     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerTagAttribute('size', 'int', 'The size of the input field');
        $this->registerTagAttribute('placeholder', 'string', 'Specifies a short hint that describes the expected value of an input element');
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', false, 'f3-form-error');
        $this->registerArgument('initialDate', 'string', 'Initial date (@see http://www.php.net/manual/en/datetime.formats.php for supported formats)');
        $this->registerUniversalTagAttributes();
    }

    /**
     * Renders the text field, hidden field and required javascript
     *
     * @param string $dateFormat
     * @param boolean $enableDatePicker
     * @return string
     */
    public function render($dateFormat = 'Y-m-d', $enableDatePicker = true)
    {
        $name = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($name);

        $this->tag->addAttribute('type', 'text');
        $this->tag->addAttribute('name', $name . '[date]');
        if ($enableDatePicker) {
            $this->tag->addAttribute('readonly', true);
        }
        $date = $this->getSelectedDate();
        if ($date !== null) {
            $this->tag->addAttribute('value', $date->format($dateFormat));
        }

        if ($this->hasArgument('id')) {
            $id = $this->arguments['id'];
        } else {
            $id = 'field' . md5(uniqid());
            $this->tag->addAttribute('id', $id);
        }
        $this->setErrorClassAttribute();
        $content = '';
        $content .= $this->tag->render();
        $content .= '<input type="hidden" name="' . $name . '[dateFormat]" value="' . htmlspecialchars($dateFormat) . '" />';

        if ($enableDatePicker) {
            $datePickerDateFormat = $this->convertDateFormatToDatePickerFormat($dateFormat);
            $content .= '<script type="text/javascript">//<![CDATA[
				$(function() {
					$("#' . $id . '").datepicker({
						dateFormat: "' . $datePickerDateFormat . '"
					}).keydown(function(e) {
							// By using "backspace" or "delete", you can clear the datepicker again.
						if(e.keyCode == 8 || e.keyCode == 46) {
							e.preventDefault();
							$.datepicker._clearDate(this);
						}
					});
				});
				//]]></script>';
        }
        return $content;
    }

    /**
     * @return \DateTime
     */
    protected function getSelectedDate()
    {
        $date = $this->getValue();
        if ($date instanceof \DateTime) {
            return $date;
        }
        if ($date !== null) {
            $date = $this->propertyMapper->convert($date, 'DateTime');
            if (!$date instanceof \DateTime) {
                return null;
            }
            return $date;
        }
        if ($this->hasArgument('initialDate')) {
            return new \DateTime($this->arguments['initialDate']);
        }
    }

    /**
     * @param string $dateFormat
     * @return string
     */
    protected function convertDateFormatToDatePickerFormat($dateFormat)
    {
        $replacements = array(
            'd' => 'dd',
            'D' => 'D',
            'j' => 'o',
            'l' => 'DD',

            'F' => 'MM',
            'm' => 'mm',
            'M' => 'M',
            'n' => 'm',

            'Y' => 'yy',
            'y' => 'y'
        );
        return strtr($dateFormat, $replacements);
    }
}
