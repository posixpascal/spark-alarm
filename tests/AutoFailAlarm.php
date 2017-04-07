<?php


use Spark\SparkAlarm;

class AutoFailAlarm extends SparkAlarm {
	public function test()
	{
		return 1 == 0;
	}
}