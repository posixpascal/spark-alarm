# 🔥spark->🚨alarm

Spark is a super simple monitoring tool with a very flexible architecture.
You can create customized alarms which trigger on your behalf and send custom notifications.
The implementation detail is minimalistic on purpose so that spark adapts to your platform.

It's designed to work as a CLI application as well as a regular shell script so you can add a
cronjob which runs spark whenever you desire.

It's also compatible by the "*cronjob tool*" many cheap VPS hoster offer where you can only
call remote PHP scripts by URL.

If you need custom server monitoring, custom notification and/or you're bound to a cheap VPS hoster – Spark is for you.


## Installation

Install using composer:
```bash 
composer require "posixpascal/spark-alarm"
```

## Usage

Spark follows the concept of custom `Alarm` classes which consist of a simple `test()` method and optional error/success handlers.
Once you have your own alarm class you can add it to the *Spark Instance* and run it.

```php
<?php

use Spark\SparkAlarm;
use Spark\Spark;

class AlertIfCPULoadIsHigh extends SparkAlarm {
    public function test(){
       return !($this->getCPULoadAverage() > 80); // not higher than 80
    }
}


class AlertIfCPULoadIsSuperHigh extends SparkAlarm {
    public function test(){
       return !$this->getCPULoadAverage() > 95; // not higher than 95
    }
    
    public function error(){ // CPU is above 95%
        mail("posixpascal@gmail.com", "CPU for server is above 95%", "Be aware, your CPU resources are low...");
    }
}


$spark = new Spark();
$spark
    ->addAlarm(new AlertIfCPULoadIsHigh())
    ->addAlarm(new AlertIfCPULoadIsSuperHigh())
    ->run();
```

Now add that script to your crontab file and you've added your first server monitoring tool.

Here is a alarm which checks if a certain website is reachable:


```php
<?php

use Spark\SparkAlarm;
use Spark\Spark;

class AlertIfWebsiteNotReachable extends SparkAlarm {
    public function test(){
         $ch = curl_init("https://google.de");
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
         curl_setopt($ch, CURLOPT_HEADER, true);
         curl_setopt($ch, CURLOPT_NOBODY, true);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         
         $response = curl_exec($ch);
         curl_close($ch);
         return !$response;
    }
    
    public function success(){
    	// website is reachable, lets log it or do sth else.
    }
    
    public function error(){ 
    	// website if off, mail here or let notifier handle the rest.
    }
}

$spark = new Spark();
$spark
    ->addAlarm(new AlertIfWebsiteNotReachable())
    ->run();
```


If you want to periodically check your alarms you can tell Spark to keep itself alive like this:

```php 
<?php 

$spark
->addAlarm(new AlertIfCPULoadIsHigh())
->addAlarm(new AlertIfCPULoadIsSuperHigh())
->keepAlive(true)
->run();

```
Now the script will periodically check your alarms and notice you if any condition fails. 
By default spark pauses for 5 minutes after each alarm was checked, you can change this delay by
passing `->interval(<seconds>)` to the spark instance.

At the end of each run spark will use a so called `Notifier` to inform you about the test results,
per default the builtin Notifier only logs failed alarms to the console, but you can easily write your own
notifier like the example below:

```php
<?php

use Spark\Notifier;
use Spark\AlarmStatus;

class MyCustomNotifier implements Notifier {
  /**
   * in this example I build a string summary and publish it to a logfile. 
   * @param $alarms array holds every failed alarm class, you can attach custom functions to your alarmclass as well.
   */
   public function send(array $alarms){
     $str = "Test results:\n";
     foreach ($alarms as $alarm){
        if ($alarm->status == AlarmStatus::SUCCESS){
        	$str .= get_class($alarm).": executed successfully @".date("Y-m-d H:i:s");
        } else {
        	$str .= get_class($alarm).": failed @".date("Y-m-d H:i:s");
        }
        $str .= "\n";
     }
     file_put_contents("/var/log/spark.log", $str);
   }
}

$spark
->notifier(new MyCustomNotifier())
->run();
```
As mentioned before Spark does only pass failed alarms to the notifier, if you want to receive all alarms you can either set
it globally like this:

```php
$spark->sendNotificationOnSuccess(true)
```

or on individual alarms by setting sendNotificationOnSuccess to true:
 
```php
<?php 
use Spark\SparkAlarm;

class AlertIfCPULoadIsHigh extends SparkAlarm  {
   public $sendNotificationOnSuccess = true;
}

```

If you are running checks using `->keepAlive` you may not always want the notifier to send you summaries.
You can set a minimum delay between notifiers as well using the `->throttle(<seconds>)` method.

Using both throttle and interval you can keep checking your alarms and execute their error function in a given interval
but only send notifications after said throttle.

I commonly use it like this:

```php 
$spark
->throttle(60 * 60) // send summaries of failed alerts every hour
->interval(60) // execute alarm tests every minute
```

Since you can access failed alarms in the notifier class you can build pretty summaries, for example
you can not only inform the user that the CPU on server1 is above 95, you can also list the processes
which consume the most CPU just by adding such a method to the alarm and calling it in the notifier.

## Helpers

SparkAlarm is the base class for an alarm and includes many utility methods for getting system resources.
These only work on Linux machines but are not necessarily required for your alarms to work.
They include:

```php
<?php 
    interface SparkAlarmHelper {
	    // cpu
    	public function getCPULoadAverage();
	
    	// disk space
	    public function getFreeDiskSpace();
	    public function getTotalDiskSpace();
	    public function getFreeDiskSpaceInPercentage();
	    
	    // memory
	    public function getTotalMemory();
	    public function getFreeMemory();
	    public function getFreeMemoryInPercentage();
	}
```

## Why

For a personal project I needed reliable server monitoring and didn't want to pay for any current solutions.
Most of the server monitoring tools on github include things like stats, DB setups, admin backends which are 
way to heavy for small personal websites.

## Todo

There are many features I'd like to implement in the future but I don't personally invest much in this library.
At the moment it's pretty barebone but I plan to add the following features if they are needed by other projects:

- [ ] Add mail notifier by default with nice templates
- [ ] Add windows and mac support for builtin SparkAlarm Helper
- [ ] Make alarms optionally dependend on another
- [ ] Keep a logfile by default

## License

MIT