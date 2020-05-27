<?php
/**
 * This file is part of pmg/queue-pheanstalk
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/Apache-2.0 Apache-2.0
 */

namespace PMG\Queue\Driver;

use Pheanstalk\Exception\ServerException;
use PMG\Queue\SimpleMessage;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\Serializer\NativeSerializer;
use PMG\Queue\Driver\Pheanstalk\ArrayOptions;
use PMG\Queue\Driver\Pheanstalk\PheanstalkEnvelope;
use PMG\Queue\Driver\Pheanstalk\PheanstalkError;

/**
 * Tests all the "happy" paths of the pheanstalk driver: no exceptions
 */
class HappyPheanstalkDriverTest extends PheanstalkTestCase
{
    private $conn, $serializer, $driver, $seenTubes = [];

    public function testDequeueReturnsNullWhenNoJobsAreFound()
    {
        $this->assertNull($this->driver->dequeue($this->randomTube()));
    }

    public function testJobsCanBeEnqueuedAndDequeuedAndRemovedWithAck()
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('NOT_FOUND');
        $tube = $this->randomTube();

        $env = $this->driver->enqueue($tube, new SimpleMessage('TestMessage'));
        $this->assertEnvelope($env);

        $env2 = $this->driver->dequeue($tube);
        $this->assertEnvelope($env2);
        $this->assertEquals('TestMessage', $env2->unwrap()->getName());

        $this->assertEquals($env->getJobId(), $env2->getJobId());

        $this->driver->ack($tube, $env2);

        // this throws so we can check for NOT_FOUND
        $this->conn->statsJob($env2->getJob());
    }

    public function testJobsCanBeEnqueuedDequeuedAndRetriedWithRetry()
    {
        $tube = $this->randomTube();

        $env = $this->driver->enqueue($tube, new SimpleMessage('TestMessage'));
        $this->assertEnvelope($env);

        $env2 = $this->driver->dequeue($tube);
        $this->assertEnvelope($env2);

        $this->assertEquals($env->getJobId(), $env2->getJobId());

        $env3 = $this->driver->retry($tube, $env2);

        // just to make sure we put the job in
        $this->conn->statsJob($env3->getJob());
    }

    /**
     * @group regression
     */
    public function testRetriedJobsDoNoPutSerializePheanstalkEnvelopes()
    {
        $tube = $this->randomTube();

        $env = $this->driver->enqueue($tube, new SimpleMessage('TestMessage'));
        $this->assertEnvelope($env);

        $env2 = $this->driver->dequeue($tube);
        $this->assertEnvelope($env2);

        $this->assertEquals($env->getJobId(), $env2->getJobId());

        $env3 = $this->driver->retry($tube, $env2);
        $this->assertEnvelope($env3);
        $this->assertInstanceOf(DefaultEnvelope::class, $env3->getWrappedEnvelope());
    }

    public function testJobsAreBuriedWithRetry()
    {
        $tube = $this->randomTube();

        $env = $this->driver->enqueue($tube, new SimpleMessage('TestMessage'));
        $this->assertEnvelope($env);

        $env2 = $this->driver->dequeue($tube);
        $this->assertEnvelope($env2);

        $this->assertEquals($env->getJobId(), $env2->getJobId());

        $this->driver->fail($tube, $env2);

        $res = $this->conn->statsJob($env2->getJob());
        $this->assertArrayHasKey('state', $res);
        $this->assertEquals('buried', $res['state']);
    }

    public function testFailWithADeleteFailureStrategyRemovesTheJob()
    {
        $driver = new PheanstalkDriver($this->conn, $this->serializer, new ArrayOptions([
            ArrayOptions::RESERVE_TIMEOUT => 1,
        ]), new Pheanstalk\DeleteFailureStrategy());
        $tube = $this->randomTube();

        $env = $driver->enqueue($tube, new SimpleMessage('TestMessage'));
        $this->assertEnvelope($env);

        $env2 = $driver->dequeue($tube);
        $this->assertEnvelope($env2);

        $this->assertEquals($env->getJobId(), $env2->getJobId());

        $driver->fail($tube, $env2);

        $this->expectException(\Pheanstalk\Exception::class);
        $this->expectExceptionMessage('NOT_FOUND');
        $this->conn->statsJob($env2->getJob());
    }

    public function testJobsCanBeReleasedAfterBeingReserved()
    {
        $tube = $this->randomTube();

        $env = $this->driver->enqueue($tube, new SimpleMessage('TestMessage'));

        $env2 = $this->driver->dequeue($tube);

        $this->driver->release($tube, $env2);

        $res = $this->conn->statsJob($env2->getJob());
        $this->assertArrayHasKey('state', $res);
        $this->assertEquals('ready', $res['state']);
    }

    protected function setUp() : void
    {
        $this->conn = self::createConnection();
        $this->serializer = NativeSerializer::fromSigningKey('supersecret');
        $this->driver = new PheanstalkDriver($this->conn, $this->serializer, new ArrayOptions([
            ArrayOptions::RESERVE_TIMEOUT => 1,
        ]));

        try {
            $this->seenTubes = array_fill_keys($this->conn->listTubes(), true);
        } catch (\Pheanstalk\Exception\ConnectionException $e) {
            $this->markTestSkipped(sprintf(
                'Beanstalkd server is not running on %s:%d',
                self::getBeanstalkdHost(),
                self::getBeanstalkdPort()
            ));
        }
    }

    private function randomTube()
    {
        do {
            $tube = uniqid('tube_', true);
        } while (isset($this->seenTubes[$tube]));

        $this->seenTubes[$tube] = true;

        return $tube;
    }

    private function assertEnvelope($env)
    {
        $this->assertInstanceOf(PheanstalkEnvelope::class, $env);
    }
}
