<?php


require_once("../spark.php");

use Spark\Alarm;
use Spark\Spark;
use Spark\SparkAlarm;

class LowCPUAlarm extends SparkAlarm implements Alarm
{
	// Alert if CPU load is less than 5, we need to heat it up.
	function test()
	{
		return $this->getCPULoadAverage() < 5;
	}

	function success()
	{
		echo "Success\n";
	}

	function error()
	{
		echo "Err\n";
	}
}

$spark = new Spark();
$spark
	->addAlarm(new LowCPUAlarm())
	->run();

