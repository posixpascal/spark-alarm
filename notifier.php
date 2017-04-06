<?php  declare(strict_types=1);

namespace Spark;

interface Notifier {
	function send(array $alarms);
}

/**
 * Helper class which simplifies getting available CPU and memory and other data
 * @package SparkAlarm
 */
class SparkNotifier {
	public function send(array $alarms){
		foreach ($alarms as $alarm){
			/**
			 * @var $alarm SparkAlarm;
			 */
			if ($alarm->status == AlarmStatus::SUCCESS) {
				echo "[" . get_class($alarm) . "]: Succeeded with " . $alarm->getNotifierSuccessMessage() . " @" . date("d.m.Y H:i:s");
			} else {
				echo "[" . get_class($alarm) . "]: Failed with " . $alarm->getNotifierErrorMessage() . " @" . date("d.m.Y H:i:s");
			}
		}
	}
}