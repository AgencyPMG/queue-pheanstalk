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

namespace PMG\Queue\Driver\Pheanstalk;

use PMG\Queue\DefaultEnvelope;
use PMG\Queue\SimpleMessage;
use PMG\Queue\Driver\PheanstalkTestCase;

class DeleteFailureStrategyTest extends PheanstalkTestCase
{
    public function testFailDeletesTheGivenPheanstalkJob()
    {
        $s = new DeleteFailureStrategy();
        $job = new \Pheanstalk\Job(123, 'ignored');
        $env = new PheanstalkEnvelope($job, new DefaultEnvelope(new SimpleMessage('ignored')));
        $conn = $this->createMock(\Pheanstalk\PheanstalkInterface::class);
        $conn->expects($this->once())
            ->method('delete')
            ->with($job);

        $s->fail($conn, $env);
    }
}
