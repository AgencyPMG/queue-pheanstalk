# pmg/queue-pheanstalk

A driver for [pmg/queue](https://github.com/AgencyPMG/Queue) backed by 
[Pheanstalk](https://github.com/pda/pheanstalk) and [Beanstalkd](http://kr.github.io/beanstalkd/).

See the [pmg/queue readme](https://github.com/AgencyPMG/Queue/blob/master/README.md)
for the documentation of how the queue system as a whole works.

See the [examples](https://github.com/AgencyPMG/queue-pheanstalk/tree/master/examples)
directory for examples of how to glue everything together.

## Quick Example

[Pheanstalk](https://github.com/pda/pheanstalk) is a PHP library for interacting
with [Beanstalkd](http://kr.github.io/beanstalkd/). `PheanstalkDriver` lets you
take advantage of Beanstalkd as a queue backend.


```php
use Pheanstalk\Pheanstalk;
use PMG\Queue\DefaultConsumer;
use PMG\Queue\Driver\PheanstalkDriver;
use PMG\Queue\Serializer\NativeSerializer;
use PMG\Queue\Serializer\SigningSerializer;

// ...

$serilizer = new NativeSerializer('this is the secret key');

$driver = new PheanstalkDriver(new \Pheanstalk\Pheanstalk('localhost'), $serializer, [
    // how long easy message has to execute in seconds
    'ttr'               => 100,

    // the "priority" of the message. High priority messages are
    // consumed first.
    'priority'          => 1024,

    // The delay between inserting the message and when it
    // becomes available for consumption
    'delay'             => 0,

    // The ttr for retries jobs
    'retry-ttr'         => 100,

    // the priority for retried jobs
    'retry-priority'    => 1024,

    // When jobs fail, they are "burieds" in beanstalkd with this priority
    'fail-priority'     => 1024,

    // A call to `dequeue` blocks for this number of seconds. A zero or
    // falsy value will block until a job becomes available
    'reserve-timeout'   => 10,
]);

// $handler instanceof PMG\Queue\MessageHandler
$consumer = new DefaultConsumer($driver, $handler);
```

## Dealing with Failed Messages

By default, `PheanstalkDriver` will [bury](https://github.com/kr/beanstalkd/blob/b7b4a6a14b7e8d096dc8cbc255b23be17a228cbb/doc/protocol.txt#L291-L293)
any message passed to `PheanstalkDriver::fail`. This is, generally, a good thing
if there are no other accountability measures around your queue system.

That said, `pmg/queue` does nothing for you regarding *kicking* jobs back to a
ready state. If there are other accountability measures around your queue
implementation and you'd rather just delete failed messages after they've been
retried, use a different `FailureStrategy`.

```php
use Pheanstalk\Pheanstalk;
use PMG\Queue\DefaultConsumer;
use PMG\Queue\Driver\PheanstalkDriver;
use PMG\Queue\Driver\Pheanstalk\DeleteFailureStrategy;
use PMG\Queue\Serializer\NativeSerializer;

// ...

$serilizer = new NativeSerializer('this is the secret key');
$failureStrategy = new DeleteFailureStrategy();

$driver = new PheanstalkDriver(new \Pheanstalk\Pheanstalk('localhost'), $serializer, [
    // as above
], $failureStrategy);

// $handler instanceof PMG\Queue\MessageHandler
$consumer = new DefaultConsumer($driver, $handler);
```

Feel free to implement `PMG\Queue\Driver\Pheanstalk\FailureStrategy` if a
different solution is needed.
