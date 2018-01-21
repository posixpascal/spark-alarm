<?php declare(strict_types=1);

namespace Spark;


/**
 * SparkAlarm is a simple yet powerful notification system which alerts
 * you when resources on your machine are low. It's designed to run
 * on linux servers and flexible to fit your needs.
 * @package Spark
 */
class Spark
{
	/**
	 *
	 * @var SparkAlarm[] Holds a list of active alarms
	 */
	protected $alarms = [];

	/**
	 * @var bool Whether or not to enable output to a certain logfile
	 */
	protected $output = false;

	/**
	 * @var bool Exit after first run or keep alive and retry with set intervals
	 */
	protected $keepAlive = false;

	/**
	 * @var int Interval between each test run (all tests are checked in a single test run)
	 */
	protected $interval = 60 * 5; // interval to check all tests

	/**
	 * @var int Delay between summarized alerts
	 */
	protected $throttle = 25 * 60; // 20min in between alerts

	/**
	 * @var int When was the last alert sent.
	 */
	protected $lastAlertSummary = 0;

	/**
	 * @var Notifier
	 */
	protected $notifier;

	/**
	 * Do not send alarms to the notifier at all
	 * @var bool
	 */
	protected $silent = false;

	/**
	 * Also send successful alarms to the notifier
	 * @var bool
	 */
	protected $sendNotificationOnSuccess = false;

	public function __construct()
	{
		$this->notifier = new SparkNotifier();
	}

	/**
	 * Change the interval between test runs, this is only required if keepAlive is set to true
	 * otherwise the cronjob determines the interval between tests
	 * @param int $interval the interval to pause between testruns in seconds
	 * @return Spark
	 */
	public function interval(int $interval): Spark
	{
		$this->interval = $interval;
		return $this;
	}

	/**
	 * Toggles whether notifier should be notified at all or not.
	 * @param bool $silent set to true to NOT notify notifier. default: false
	 * @return Spark
	 */
	public function silent($silent) : Spark {
		$this->silent = $silent;
		return $this;
	}

	/**
	 * Toggles whether notifier should receive successful alarms or not
	 * @param bool $status set to true to send successful alarms to notifier (default: false)
	 * @return $this
	 */
	public function sendNotificationsOnSuccess(bool $status)
	{
		$this->sendNotificationOnSuccess = $status;
		return $this;
	}

	/**
	 * Set a custom notifier to handle failed alarms
	 * @param SparkNotifier $notifier
	 * @return Spark
	 */
	public function notifier(SparkNotifier $notifier): Spark
	{
		$this->notifier = $notifier;
		return $this;
	}

	/**
	 * The minimum delay to pass between notifier events, only if keepAlive is true
	 * if keepalive is false then the cronjob determines the minimum throttle.
	 * @param int $throttle amount of seconds to pass before notifier is informed again (default: 1 hour)
	 * @return Spark
	 */
	public function throttle(int $throttle): Spark
	{
		$this->throttle = $throttle;
		return $this;
	}

	/**
	 * Whether or not spark should keep 
	 ning for ever continously checking alarms
	 * this is useful if you want to start spark as a system daemon at startup
	 * @param bool $keepAlive whether or not to enable keepalive.
	 * @return Spark
	 */
	public function keepAlive(bool $keepAlive): Spark
	{
		$this->keepAlive = $keepAlive;
		if ($keepAlive) {
			set_time_limit(0);
		}
		return $this;
	}

	/**
	 * Add a alarm
	 * @param SparkAlarm $alarm
	 * @return Spark
	 */
	public function addAlarm($alarm): Spark
	{
		$this->alarms[] = $alarm;
		return $this;
	}

	/**
	 * Get a list of alarms
	 * @return SparkAlarm[]
	 */
	public function getAlarms(){
		return $this->alarms;
	}

	/**
	 * Start spark
	 * @return array (list of notifications which were sent to the notifier)
	 */
	public function run()
	{
		$notifications = [];

		$this->lastAlertSummary = 0;

		do {
			foreach ($this->alarms as $alarm) {
		
				if ($alarm->test()) {
					$alarm->success();
					$alarm->status(SparkAlarmStatus::SUCCESS);

					if ($this->sendNotificationOnSuccess || $alarm->sendNotificationOnSuccess) {
						$notifications[] = $alarm;
					}
					continue;
				}
				
				$notifications[] = $alarm;
				$alarm->status(SparkAlarmStatus::ERROR);
				$alarm->error();
			}

			if (!$this->silent) {
				$this->sendSummary($notifications);
			}

			if ($this->keepAlive) {
				sleep($this->interval);
			}
		} while ($this->keepAlive);
		return $notifications;
	}

	/**
	 * Inform the notifier with given alarms
	 * @param $failedAlarms
	 */
	private function sendSummary($failedAlarms)
	{
		if (time() - $this->lastAlertSummary >= $this->throttle) {
			$this->notifier->send($failedAlarms);
			$this->lastAlertSummary = time();
		}
	}

	/**
	 * Remove an alarm class from the list of alarms
	 * @param $removeAlarm (class instance)
	 * @return $this
	 */
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

	/**
	 * Set log output
	 * @param $output
	 * @return $this
	 */
	public function output($output)
	{
		$this->output = $output;
		return $this;
	}
}
