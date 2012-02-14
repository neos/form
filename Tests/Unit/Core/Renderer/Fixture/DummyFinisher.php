<?php
namespace TYPO3\Form\Tests\Unit\Core\Runtime\Renderer\Fixture;

use TYPO3\Form\Core\Model\FinisherContext;

class DummyFinisher implements \TYPO3\Form\Core\Model\FinisherInterface {

	public $cb = NULL;

	public function execute(FinisherContext $finisherContext) {
		$cb = $this->cb;
		$cb($finisherContext);
	}
	public function setOptions(array $options) {

	}
}
?>