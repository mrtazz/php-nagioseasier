# nagioseasier-php

## Overview
PHP library that wraps the
[nagioseasier][https://github.com/wfarr/nagioseasier-module] query handler for
nagios4

## Usage
```
Nagioseasier::status($hostname or $service);
Nagioseasier::check($hostname or $service);
Nagioseasier::enable_notifications($hostname or $service);
Nagioseasier::disable_notifications($hostname or $service);
Nagioseasier::acknowledge($hostname or $service, [$comment]);
Nagioseasier::unacknowledge($hostname or $service);
Nagioseasier::downtime($hostname or $service, [$minutes, $comment]);
Nagioseasier::problems($hostgroup or $servicegroup, [$state]);
```

## Installation
Download and include `nagioseasier.php` somewhere in your path.
