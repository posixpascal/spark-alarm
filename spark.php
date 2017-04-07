<?php declare(strict_types=1);

namespace Spark;

require_once(dirname(__FILE__) . "/alarm.php");
require_once(dirname(__FILE__) . "/notifier.php");
require_once(dirname(__FILE__) . "/status.php");

/**
 * SparkAlarm is a simple yet powerful notification system which alerts
 * you when resources on your machine are low. It's designed to run
 * on linux servers and flexible to fit your needs.
 */
class Spark
{
	/**
	 *
	 * @var SparkAlarm[] Holds a list of active alarms
	 */
	private $alarms = [];

	/**
	 * @var bool Whether or not to enable output to a certain logfile
	 */
	private $output = false;

	/**
	 * @var bool Exit after first run or keep alive and retry with set intervals
	 */
	private $keepAlive = false;

	/**
	 * @var int Interval between each test run (all tests are checked in a single test run)
	 */
	private $interval = 60 * 5; // interval to check all tests

	/**
	 * @var int Delay between summarized alerts
	 */
	private $throttle = 25 * 60; // 20min in between alerts

	/**
	 * @var int When was the last alert sent.
	 */
	private $lastAlertSummary = 0;

	/**
	 * @var Notifier
	 */
	private $notifier;

	private $sendNotificationOnSuccess = false;

	public function __construct()
	{
		$this->notifier = new SparkNotifier();
	}

	public function interval(int $interval): Spark
	{
		$this->interval = $interval;
		return $this;
	}

	public function sendNotificationsOnSuccess(bool $status)
	{
		$this->sendNotificationOnSuccess = $status;
		return $this;
	}

	public function notifier(Notifier $notifier): Spark
	{
		$this->notifier = $notifier;
		return $this;
	}

	public function throttle(int $throttle): Spark
	{
		$this->throttle = $throttle;
		return $this;
	}


	public function keepAlive(bool $keepAlive): Spark
	{
		$this->keepAlive = $keepAlive;
		if ($keepAlive) {
			set_time_limit(0);
		}
		return $this;
	}

	public function addAlarm($alarm): Spark
	{
		$this->alarms[] = $alarm;
		return $this;
	}

	public function run(): Spark
	{
		$failed = [];
		$success = [];
		$this->lastAlertSummary = 0;
		do {
			foreach ($this->alarms as $alarm) {
				if ($alarm->test()) {
					$alarm->success();
					$alarm->status(AlarmStatus::SUCCESS);
					if ($this->sendNotificationOnSuccess || $alarm->sendNotificationOnSuccess) {
						$success[] = $alarm;
					}
				} else {
					$failed[] = $alarm;
					$alarm->status(AlarmStatus::ERROR);
					$alarm->error();
				}
			}

			$notifications = array_merge($failed, $success);
			$this->sendSummary($notifications);

			if ($this->keepAlive) {
				sleep($this->interval);
			}
		} while ($this->keepAlive);
		return $this;
	}

	private function sendSummary($failedAlarms)
	{
		if (time() - $this->lastAlertSummary >= $this->throttle) {
			$this->notifier->send($failedAlarms);
			$this->lastAlertSummary = time();
		}
	}

	public function removeAlarm($removeAlarm)
	{
		$newAlarms = [];
		foreach ($this->alarms as $alarm) {
			if (get_class($alarm) !== get_class($removeAlarm)) {
				$newAlarms[] = $alarm;
			}
		}
		$this->alarms = $newAlarms;
		return $this;
	}

	public function output($output)
	{
		$this->output = $output;
		return $this;
	}
}
