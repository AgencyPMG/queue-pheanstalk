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

use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use PMG\Queue\SimpleMessage;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\Exception\InvalidEnvelope;
use PMG\Queue\Serializer\NativeSerializer;
use PMG\Queue\Driver\Pheanstalk\PheanstalkEnvelope;
use PMG\Queue\Driver\Pheanstalk\PheanstalkError;
use PMG\Queue\Driver\Pheanstalk\PheanstalkOptions;

/**
 * Tests all the "unhappy" paths for the pheanstalk driver. This test
 * purposefully tries to cause errors by giving invalid hosts, etc.
 */
class UnhappyPheanstalkDriverTest extends PheanstalkTestCase
{
    private $conn, $serializer, $driver;

    public function testAckCannotBeCalledWithABadEnvelope()
    {
        $this->expectException(InvalidEnvelope::class);
        $this->driver->ack('q', new DefaultEnvelope(new SimpleMessage('t')));
    }

    public function testRetryCannotBeCalledWithABadEnvelope()
    {
        $this->expectException(InvalidEnvelope::class);
        $this->driver->retry('q', new DefaultEnvelope(new SimpleMessage('t')));
    }

    public function testFailCannotBeCalledWithABadEnvelope()
    {
        $this->expectException(InvalidEnvelope::class);
        $this->driver->fail('q', new DefaultEnvelope(new SimpleMessage('t')));
    }

    public function testEnqueueErorrsWhenTheUnderlyingConnectionErrors()
    {
        $this->expectException(PheanstalkError::class);
        $this->driver->enqueue('q', new SimpleMessage('test'));
    }

    public function testDequeueErorrsWhenTheUnderlyingConnectionErrors()
    {
        $this->expectException(PheanstalkError::class);
        $this->driver->dequeue('q', new SimpleMessage('test'));
    }

    public function testAckErrorsWhenUnderlyingConnectionErrors()
    {
        $this->expectException(PheanstalkError::class);
        $this->driver->ack('q', $this->env);
    }

    public function testRetryErrorsWhenUnderlyingConnectionErrors()
    {
        $this->expectException(PheanstalkError::class);
        $this->driver->retry('q', $this->env);
    }

    public function testFailErrorsWhenUnderlyingConnectionErrors()
    {
        $this->expectException(PheanstalkError::class);
        $this->driver->fail('q', $this->env);
    }

    public function testReleaseErrorsWhenTheUnerlyingConnectionErrors()
    {
        $this->expectException(PheanstalkError::class);
        $this->driver->release('q', $this->env);
    }

    /**
     * @group legacy
     */
    public function testDriverCanStillBeCreatedWithAnArrayHasOptions()
    {
        $driver = new PheanstalkDriver($this->conn, $this->serializer, [
            PheanstalkOptions::RESERVE_TIMEOUT => 1,
        ]);

        $this->assertInstanceOf(PheanstalkDriver::class, $driver, 'just to get get PHPUnit to not say this test is risky');
    }

    protected function setUp() : void
    {
        $this->conn = Pheanstalk::create('localhost', 65000);
        $this->serializer = NativeSerializer::fromSigningKey('supersecret');
        $this->driver = new PheanstalkDriver($this->conn, $this->serializer);
        $this->env = new PheanstalkEnvelope(
            new Job(123, 't'),
            new DefaultEnvelope(new SimpleMessage('t'))
        );
    }
}
