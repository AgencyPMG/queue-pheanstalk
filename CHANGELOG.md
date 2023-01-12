# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 6.0.0

### Changed

- `PMG\Queue\Driver\PheanstalkDriver` no longer accepts arrays as its `$options`
  argument, instead pass `PMG\Queue\Driver\Pheanstalk\ArrayOptions`.
- `PMG\Queue\Driver\Pheanstalk\BuryFailureStrategy` no longer accepts an integer
  argument to its constructor, pass an options instance instead.

## 5.2.0

### Added 

- Add support for PHP 8.X

### Changed

- Dropped PHP 7.3 support

## 5.1.0

### Added

- `PMG\Queue\Driver\Pheanstalk\PheanstalkOptions` interface as added along with
  an implementation backed by an array (`ArrayOptions`). This should allow
  end-users to change message options (things like priority, etc) based on
  incoming messages.

### Deprecated

- Passing an array of options to `PheanstalkDriver`'s constructor is deprecated,
  use a `PheanstalkOptions` implementation instead (probably `ArrayOptions`)
- Passing null or an integer to `BuryFailureStrategy` is deprecated, pass a
  `PheanstalkOptions` implementation instead.

## 5.0.0

**Important Note:** we're skipping v4.X because I'm tired of trying to figure
out which version of my own queue library goes with which driver. If it's a 5.X
version of any `pmg/queue` library it will work with `pmg/queue` 5.X.

### Changed

- PHP 7.3+ is now required.
- `pmg/queue` 5.X is now required, and the drivers method signatures have
    changed to reflect the `Driver` interface changes in `pmg/queue` 5.X
- The `retry-delay` option was removed. The `pmg/queue` core now supports
  retry delays and the driver now uses that system.

### Fixed
n/a

### Added
n/a

## 3.0.0

### Changed

- Pheanstalk 4.X is required to use the driver
- Dropped support for PHP 7.0 and 7.1

## 1.1.0

### Changed
n/a

### Fixed
n/a

### Added

- Introduces a new `FailureStrategy` interface as a pluggable way to change how
  the driver deals with failed message. The default behavior is to `BURY`
  them, though a `DELETE` strategy is also supplied. Pass a (optiona)
  `FailureStrategy` instance as the fourth argument of `PheanstalkDriver::__construct`.

## 1.0.0

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
