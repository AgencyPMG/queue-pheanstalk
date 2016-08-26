<?php

use PMG\Queue;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/StreamLogger.php';

$conn = new \Pheanstalk\Pheanstalk('localhost');
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
$serializer = new Queue\Serializer\NativeSerializer('SuperSecretKey', $allowedClasses);
$driver = new Queue\Driver\PheanstalkDriver($conn, $serializer);

$router = new Queue\Router\MappingRouter([
    'TestMessage'   => $queueName,
    'TestMessage2'  => $queueName,
    'MustStop'      => $queueName,
]);

$handler = new Queue\Handler\CallableHandler(function (Queue\Message $msg) {
    $name = $msg->getName();
    if ('MustStop' === $name) {
        throw new Queue\Exception\SimpleMustStop();
    }

    echo $name, PHP_EOL;
});

$producer = new Queue\DefaultProducer($driver, $router);

$consumer = new Queue\DefaultConsumer(
    $driver,
    $handler,
    new Queue\Retry\NeverSpec(),
    new StreamLogger()
);

$producer->send(new Queue\SimpleMessage('TestMessage'));
$producer->send(new Queue\SimpleMessage('TestMessage2'));
$producer->send(new Queue\SimpleMessage('MustStop'));

exit($consumer->run($queueName));
