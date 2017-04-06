<?php namespace Spark\Examples;

require_once("../spark.php");

use Spark\Alarm;
use Spark\Spark;
use Spark\SparkAlarm;

class LowCPU extends SparkAlarm implements Alarm
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

class RandAlarm extends SparkAlarm implements Alarm
{
	public $sendNotificationOnSuccess = true;
	// Alert if CPU load is less than 5, we need to heat it up.
	function test()
	{
		return rand(0, 10) < rand(0, 10);
	}

	function success()
	{
		echo "Success\n";
	}

	function error()
	{
		echo "Err\n";
	}

	function getNotifierErrorMessage(){
		return "OOPS! I failed :(";
	}
}


$spark = new Spark();
$spark
	->addAlarm(new LowCPU())
	->addAlarm(new RandAlarm())
	->throttle(5)
	->interval(2)
	->output("spark.log")
	->keepAlive(true)
	->run();

