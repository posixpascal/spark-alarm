# spark->alarm!

Spark is a simple monitoring tool with a very flexible architecture.
You can create customized alarms which trigger on your behalf and send custom notifications.
The implementation detail is minimalistic on purpose so that you implement your own spark handlers.

[toc]

## Installation

tbd.

## The simpliest example

Write a custom SparkAlarm class like so:

```php
<?php

use Spark\SparkAlarm;
use Spark\Spark;

class AlertIfCPUIsLow extends Alarm {
    public function test(){
       if ($this->getCPULoadAverage() > 80){
           return false;
       }
       return true;
    }
}


class AlertIfCPUIsSuperLow extends SparkAlarm {
    public function test(){
       if ($this->getCPULoadAverage() > 95){
           return false;
       }
       return true;
    }
    
    error(){ // CPU is above 95%
        sendmail(..)
    }
}


$spark = new Spark();
$spark
    ->addAlarm(new AlertIfCPUIsLow())
    ->addAlarm(new AlertIfCPUIsSuperLow())
    ->run();
```


If you execute it by lets say a cronjob and your CPU is above 80, spark will just print a message that the alarm failed.
If the CPU is above 95, spark will execute the error handler accordinly and send an email.

You can tell spark to check indefinitely using `->keepAlive()`:

```php 
<?php 

$spark
->addAlarm(new AlertIfCPUIsLow())
->addAlarm(new AlertIfCPUIsSuperLow())
->keepAlive(true)
->run();

```

This will execute the tests in a given interval which can be changed using the `->interval($secs)` method.

Spark supports custom notifier if you want to get bundled "summaries". By default the SparkNotifier class is very barebone and just prints everything into the console.
You can write your own notifier easily by subclassing the existing notifier:


```php
<?php
class MyNotifier implements Notifier {
   public send($alarms){
     $str = "Test results:\n";
     foreach ($alarms as $alarm){
        $str .= "Test result for: ".get_class($alarm).": ". ($alarm->status == AlarmStatus::SUCCESS) ? "successful" : "not successful";
     }
     // do something with $str
   }
}

$spark
->notifier(new MyNotifier())
->run();
```

By default the notifier receives only failed posts, you can change this behaviour by setting:
`$spark->sendNotificationOnSuccess(true)` globally or by setting the attribute on a class individually:

```php
<?php 
class MyNotifier {
   public $sendNotificationOnSuccess = true;
}

```

When running with keepAlive you don't want to get spammed by your notifier do you? Spark throttles the notifier by default so that only one notification goes through every 25minutes.
You can change this behavior as well using: `$spark->throttle(0)` ;).

## Why

I needed a super simple custom server monitoring tool for a few projects which works well with CRON and manually as a script.
Since I didn't find anything on github that suits my needs I created one from scratch.

## Todo
This is far from finished and it will probably not change within the next months.  
Windows support is non existend and Mac support is untested.

It works for my simple use case at the moment, I might extend it if the project needs to grow.


## License

MIT