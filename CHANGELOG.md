# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 3.0.0 (Unreleased)

### Changed

- [BC BREAK] Migrated this driver from the main pmg/queue repository
- [BC BREAK] The order of arguments in `PheanstalkDriver`'s constructor changed.
  You *must* pass a `PMG\Queue\Serializer` instance as the second argument now.

```php
use PMG\Queue\Driver\PheanstalkDriver;
use PMG\Queue\Serializer\NativeSerializer;

$conn = new \Pheanstalk\Pheanstalk();

// pmg/queue 2.X
$driver = new PheanstalkDriver($conn, [
    // driver opts
]);

// pmg/queue 3.X
$serializer = new NativeSerializer('SuperSecretKey');
$driver = new PheanstalkDriver($conn, $serializer, [
    // driver opts
]);
```

### Fixed

n/a

### Added

n/a
