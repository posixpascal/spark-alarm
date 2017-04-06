<?php


require_once("../spark.php");

use Spark\Alarm;
use Spark\Spark;
use Spark\SparkAlarm;

class MyCustomAlarm extends SparkAlarm implements Alarm {
	function test(){
		return 1 == 1;
	}

	function success(){
		echo "Success\n";
	}

	function error(){
		echo "Err\n";
	}
}

$spark = new Spark();
$spark->addAlarm(new MyCustomAlarm());
$spark->run();