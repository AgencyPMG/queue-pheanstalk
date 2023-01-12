<?php

use PMG\Queue;
use PMG\Queue\Driver\Pheanstalk\ArrayOptions;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Contract\PheanstalkInterface;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/StreamLogger.php';

$conn = Pheanstalk::create('localhost');
$tubes = $conn->listTubes();
do {
    $queueName = uniqid('example_');
} while (in_array($queueName, $tubes, true));

// native serializer supports allowed classes in PHP 7+
$allowedClasses = null;
if (PHP_VERSION_ID >= 70000) {
    $allowedClasses = array_merge([
        Queue\SimpleMessage::class,
    ], Queue\Driver\PheanstalkDriver::allowedClasses());
}
$serializer = Queue\Serializer\NativeSerializer::fromSigningKey('SuperSecretKey', $allowedClasses);
$driver = new Queue\Driver\PheanstalkDriver($conn, $serializer, new ArrayOptions([
    'retry-priority' => 0, // most urgent
]));

$router = new Queue\Router\MappingRouter([
    'TestMessage'   => $queueName,
    'MustStop'      => $queueName,
]);

$handler = new Queue\Handler\CallableHandler(function (Queue\Message $msg) {
    $name = $msg->getName();
    if ('MustStop' === $name) {
        throw new Queue\Exception\SimpleMustStop();
    }

    throw new \Exception('errors get retried');
});

$producer = new Queue\DefaultProducer($driver, $router);

$consumer = new Queue\DefaultConsumer(
    $driver,
    $handler,
    new Queue\Retry\LimitedSpec(3),
    new StreamLogger()
);

$producer->send(new Queue\SimpleMessage('TestMessage'));
$producer->send(new Queue\SimpleMessage('MustStop'));

exit($consumer->run($queueName));
