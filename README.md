# nagioseasier-php

## Overview
PHP library that wraps the
[nagioseasier][https://github.com/wfarr/nagioseasier-module] query handler for
nagios4.

## Usage
```
<?php

require "nagioseasier.php";

$details = Nagioseasier::status($hostname or $service);
$details = Nagioseasier::check($hostname or $service);
$details = Nagioseasier::enable_notifications($hostname or $service);
$details = Nagioseasier::disable_notifications($hostname or $service);
$details = Nagioseasier::acknowledge($hostname or $service, [$comment]);
$details = Nagioseasier::unacknowledge($hostname or $service);
$details = Nagioseasier::downtime($hostname or $service, [$minutes, $comment]);
$details = Nagioseasier::problems($hostgroup or $servicegroup, [$state]);
```

## Installation
Download and include `nagioseasier.php` somewhere in your path.
