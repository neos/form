<?php
namespace Neos\Form\Tests\Functional\Fixtures\FormFactories;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Factory\AbstractFormFactory;

class TwoPageFormWithUploadFactory extends AbstractFormFactory
{
    public function build(array $configuration, $presetName)
    {
        $formDefinition = new FormDefinition('two-page-form-with-upload', $this->getPresetConfiguration($presetName));

        $page1 = $formDefinition->createPage('page1');
        $page2 = $formDefinition->createPage('page2');

        $fileUpload = $page1->createElement('file', 'Neos.Form:FileUpload');
        $fileUpload->setProperty('allowedExtensions', ['txt']);
        $page1->createElement('date', 'Neos.Form:DatePicker');
        $page2->createElement('text2-1', 'Neos.Form:SingleLineText');

        return $formDefinition;
    }
}
