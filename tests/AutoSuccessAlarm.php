<?php


use Spark\SparkAlarm;

class AutoSuccessAlarm extends SparkAlarm {
	public function test()
	{
		return 1 == 1;
	}
}