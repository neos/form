<?php
namespace Neos\Form\Core\Runtime;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Utility\Arrays;

/**
 * The current state of the form which is attached to the {@link FormRuntime}
 * and saved in a session or the client.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * @internal
 */
class FormState
{
    /**
     * Constant which means that we are currently not on any page; i.e. the form
     * has never rendered before.
     */
    const NOPAGE = -1;

    /**
     * The last displayed page index
     *
     * @var integer
     */
    protected $lastDisplayedPageIndex = self::NOPAGE;

    /**
     * @var array
     */
    protected $formValues = [];

    /**
     * @return boolean FALSE if the form has never been submitted before, TRUE otherwise
     */
    public function isFormSubmitted()
    {
        return ($this->lastDisplayedPageIndex !== self::NOPAGE);
    }

    /**
     * @return integer
     */
    public function getLastDisplayedPageIndex()
    {
        return $this->lastDisplayedPageIndex;
    }

    /**
     * @param integer $lastDisplayedPageIndex
     * @return void
     */
    public function setLastDisplayedPageIndex($lastDisplayedPageIndex)
    {
        $this->lastDisplayedPageIndex = $lastDisplayedPageIndex;
    }

    /**
     * @return array
     */
    public function getFormValues()
    {
        return $this->formValues;
    }

    /**
     * @param string $propertyPath
     * @param mixed $value
     * @return void
     */
    public function setFormValue($propertyPath, $value)
    {
        $this->formValues = Arrays::setValueByPath($this->formValues, $propertyPath, $value);
    }

    /**
     * @param string $propertyPath
     * @return mixed
     */
    public function getFormValue($propertyPath)
    {
        return Arrays::getValueByPath($this->formValues, $propertyPath);
    }
}
