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
 * Displays two select-boxes for hour and minute selection.
 */
class TimePickerViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'select';

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
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerTagAttribute('size', 'int', 'The size of the select field');
		$this->registerTagAttribute('placeholder', 'string', 'Specifies a short hint that describes the expected value of an input element');
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the select element should be disabled when the page loads');
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
		$this->registerArgument('initialDate', 'string', 'Initial time (@see http://www.php.net/manual/en/datetime.formats.php for supported formats)');
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Renders the select fields for hour & minute
	 *
	 * @return string
	 */
	public function render() {
		$name = $this->getName();
		$this->registerFieldNameForFormTokenGeneration($name);

		$this->tag->addAttribute('name', $name . '[hour]');
		$date = $this->getSelectedDate();
		$this->setErrorClassAttribute();

		$content = '';
		$content .= $this->buildHourSelector($date);
		$content .= $this->buildMinuteSelector($date);
		return $content;
	}

	/**
	 * @return \DateTime
	 */
	protected function getSelectedDate() {
		$date = $this->getValue();
		if ($date instanceof \DateTime) {
			return $date;
		}
		if ($date !== NULL) {
			$date = $this->propertyMapper->convert($date, 'DateTime');
			if (!$date instanceof \DateTime) {
				return NULL;
			}
			return $date;
		}
		if ($this->hasArgument('initialDate')) {
			return new \DateTime($this->arguments['initialDate']);
		}
	}

	/**
	 * @param \DateTime $date
	 * @return string
	 */
	protected function buildHourSelector(\DateTime $date = NULL) {
		$value = $date !== NULL ? $date->format('H') : NULL;
		$hourSelector = clone $this->tag;
		$hourSelector->addAttribute('name', sprintf('%s[hour]', $this->getName()));
		$options = '';
		foreach(range(0, 23) as $hour) {
			$hour = str_pad($hour, 2, '0', STR_PAD_LEFT);
			$selected = $hour === $value ? ' selected="selected"' : '';
			$options .= '<option value="' . $hour . '"'.$selected.'>' . $hour . '</option>';
		}
		$hourSelector->setContent($options);
		return $hourSelector->render();
	}

	/**
	 * @param \DateTime $date
	 * @return string
	 */
	protected function buildMinuteSelector(\DateTime $date = NULL) {
		$value = $date !== NULL ? $date->format('i') : NULL;
		$minuteSelector = clone $this->tag;
		if ($this->hasArgument('id')) {
			$minuteSelector->addAttribute('id', $this->arguments['id'] . '-minute');
		}
		$minuteSelector->addAttribute('name', sprintf('%s[minute]', $this->getName()));
		$options = '';
		foreach(range(0, 59) as $minute) {
			$minute = str_pad($minute, 2, '0', STR_PAD_LEFT);
			$selected = $minute === $value ? ' selected="selected"' : '';
			$options .= '<option value="' . $minute . '"'.$selected.'>' . $minute . '</option>';
		}
		$minuteSelector->setContent($options);
		return $minuteSelector->render();
	}

}
