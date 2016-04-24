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

use Pheanstalk\Job;
use PMG\Queue\Envelope;
use PMG\Queue\Message;

/**
 * The envelope that backs the Pheanstalk driver. This provides an additional
 * property to keep track of the job ID. This wraps another Envelope object
 * that comes back from the queue serialized.
 *
 * @since   2.0
 */
final class PheanstalkEnvelope implements Envelope
{
    /**
     * @var Job
     */
    private $job;

    /**
     * @var Envelope
     */
    private $wrapped;

    public function __construct(Job $job, Envelope $wrapped)
    {
        $this->job = $job;
        $this->wrapped = $wrapped;
    }

    /**
     * {@inheritdoc}
     */
    public function unwrap()
    {
        return $this->wrapped->unwrap();
    }

    /**
     * {@inheritdoc}
     */
    public function attempts()
    {
        return $this->wrapped->attempts();
    }

    /**
     * {@inheritdoc}
     * Returns a clone of the wrapped envelope, not itself.
     */
    public function retry()
    {
        return $this->wrapped->retry();
    }

    public function getJob()
    {
        return $this->job;
    }

    public function getJobId()
    {
        return $this->getJob()->getId();
    }
}