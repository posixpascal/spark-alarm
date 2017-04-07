<?php declare(strict_types=1);

namespace Spark;

interface Alarm
{
	function test();
}

/**
 * Helper class which simplifies getting available CPU and memory and other data
 * @package Spark
 */
class SparkAlarm implements Alarm
{
	/**
	 * Which directory should be checked when accessing free disk space
	 */
	const DEFAULT_PARTITION_DIRECTORY = "/";

	/**
	 * @var SparkAlarmStatus $status Determines whether the alarm failed or not
	 */
	public $status;

	/**
	 * Send to notifier if this alarm succeeded.
	 * @var bool
	 */
	public $sendNotificationOnSuccess = false;

	/**
	 * Set the alarm status
	 * @param int $status
	 */
	public function status(int $status){
		$this->status = $status;
	}

	/**
	 * Stub for the success event handler
	 */
	public function success()
	{
	}

	/**
	 * Stub for the error event handler
	 */
	public function error()
	{
	}

	/**
	 * Stub for the test handler
	 * @return bool
	 */
	public function test(){
		return false;
	}

	/**
	 * Returns the average CPU load as an integer (from 0 - 100)
	 * @return mixed
	 */
	public function getCPULoadAverage()
	{
		return sys_getloadavg()[0];
	}

	/**
	 * Get the free disk space in bytes
	 * @return bool|float
	 */
	public function getFreeDiskSpace()
	{
		return disk_free_space(SparkAlarmStatus::DEFAULT_PARTITION_DIRECTORY);
	}

	/**
	 * Get the total disk space in bytes
	 * @return bool|float
	 */
	public function getTotalDiskSpace()
	{
		return disk_total_space(SparkAlarmStatus::DEFAULT_PARTITION_DIRECTORY);
	}

	/**
	 * Get the  free disk space as a percentage value (0-100)
	 * @return float
	 */
	public function getFreeDiskSpaceInPercentage()
	{
		return round($this->getFreeDiskSpace() * 100 / $this->getTotalDiskSpace());
	}

	/**
	 * Get total amount of memory in kB (linux only)
	 * @return int|mixed
	 */
	public function getTotalMemory()
	{
		return $this->getMemoryByIdentifier("MemTotal");
	}

	/**
	 * Get free amount of memory in kB (linux only)
	 * @return int|mixed
	 */
	public function getFreeMemory()
	{
		return $this->getMemoryByIdentifier("MemFree");
	}

	/**
	 * Get free memory in percentage (0-100)
	 * @return float
	 */
	public function getFreeMemoryInPercentage()
	{
		$freeMemory = $this->getFreeMemory();
		$totalMemory = $this->getTotalMemory();

		return round(floatval($freeMemory) * 100 / floatval($totalMemory));
	}

	/**
	 * Helper to read proc/meminfo on linux systems.
	 * @param $name
	 * @return int|mixed
	 */
	private function getMemoryByIdentifier($name)
	{
		$fh = fopen('/proc/meminfo', 'r');
		$mem = 0;
		while ($line = fgets($fh)) {
			$pieces = array();
			if (preg_match('/^' . $name . ':\s+(\d+)\skB$/', $line, $pieces)) {
				$mem = $pieces[1];
				break;
			}
		}
		fclose($fh);
		return $mem;
	}


	/**
	 * Custom notifier success message (used for the default notifier)
	 * @return string
	 */
	public function getNotifierSuccessMessage()
	{
		return "I succeeded";
	}

	/**
	 * Custom notifier error message
	 * @return string
	 */
	public function getNotifierErrorMessage()
	{
		return "Oops, it failed";
	}
}