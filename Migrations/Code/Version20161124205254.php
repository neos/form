<?php
namespace Neos\Flow\Core\Migrations;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * Adjusts code to package renaming from "TYPO3.Form" to "Neos.Form"
 */
class Version20161124205254 extends AbstractMigration
{

	public function getIdentifier()
	{
		return 'Neos.Form-20161124205254';
	}

	/**
	 * @return void
	 */
	public function up()
	{
		$this->searchAndReplace('TYPO3\Form', 'Neos\Form');
		$this->searchAndReplace('TYPO3.Form', 'Neos.Form');

		$this->moveSettingsPaths('TYPO3.Form', 'Neos.Form');
	}
}
