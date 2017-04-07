<?php


use Spark\SparkNotifier;

class TestNotifier extends SparkNotifier {
	public $receivedAlarms = [];
	public function send(array $alarms)
	{
		$this->receivedAlarms = $alarms;
	}
}