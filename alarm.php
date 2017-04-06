<?php declare(strict_types=1);

namespace Spark;

interface Alarm
{
	function test();
}

/**
 * Helper class which simplifies getting available CPU and memory and other data
 * @package SparkAlarm
 */
class SparkAlarm implements Alarm
{
	const DEFAULT_PARTITION_DIRECTORY = "/";

	public $status;
	public $sendNotificationOnSuccess = false;

	public function status(int $status){
		$this->status = $status;
	}

	public function success()
	{
	}

	public function error()
	{
	}

	public function test(){
		return false;
	}

	public function getCPULoadAverage()
	{
		return sys_getloadavg()[0];
	}

	public function getFreeDiskSpace()
	{
		return disk_free_space(SparkAlarm::DEFAULT_PARTITION_DIRECTORY);
	}

	public function getTotalDiskSpace()
	{
		return disk_total_space(SparkAlarm::DEFAULT_PARTITION_DIRECTORY);
	}

	public function getFreeDiskSpaceInPercentage()
	{
		return round($this->getFreeDiskSpace() * 100 / $this->getTotalDiskSpace());
	}

	public function getTotalMemory()
	{
		return $this->getMemoryByIdentifier("MemTotal");
	}

	public function getFreeMemory()
	{
		return $this->getMemoryByIdentifier("MemFree");
	}

	public function getFreeMemoryInPercentage()
	{
		$freeMemory = $this->getFreeMemory();
		$totalMemory = $this->getTotalMemory();

		return round(floatval($freeMemory) * 100 / floatval($totalMemory));
	}

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


	public function getNotifierSuccessMessage()
	{
		return "I succeeded";
	}

	public function getNotifierErrorMessage()
	{
		return "Oops, it failed";
	}
}