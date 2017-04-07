<?php
use Spark\Spark;

require_once(dirname(__FILE__)."/../vendor/autoload.php");
require_once(dirname(__FILE__)."/AutoFailAlarm.php");
require_once(dirname(__FILE__)."/AutoSuccessAlarm.php");
require_once(dirname(__FILE__)."/TestNotifier.php");


class SparkTest extends PHPUnit\Framework\TestCase {

	public function testAlarms()
	{
		$spark = new Spark();
		$spark->silent(true);
		$spark->addAlarm(new AutoFailAlarm());
		$this->assertEquals(1, count($spark->getAlarms()), "there should be exactly 1 alarm in spark");

		$results = $spark->run();

		$this->assertEquals(1, count($results), "exactly 1 alarm should fail");
	}

	public function testNotifier()
	{
		$notifier = new TestNotifier();

		$spark = new Spark();
		$spark
			->notifier($notifier)
			->addAlarm(new AutoFailAlarm())
			->run();

		$this->assertEquals(1, count($notifier->receivedAlarms), "exactly 1 alarm should be received by the notifier");
		$spark
			->addAlarm(new AutoFailAlarm())
			->run();

		$this->assertEquals(2, count($notifier->receivedAlarms), "exactly 2 alarms should be received by the notifier");

		$spark
			->addAlarm(new AutoSuccessAlarm())
			->run();

		$this->assertEquals(2, count($notifier->receivedAlarms), "exactly 2 failed alarms should be received by the notifier");


		$spark
			->sendNotificationsOnSuccess(true)
			->run();

		$this->assertEquals(3, count($notifier->receivedAlarms), "exactly 3 alarms (including success alarms) should be received by the notifier");
	}


}