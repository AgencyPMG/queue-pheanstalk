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

use Pheanstalk\Contract\PheanstalkInterface;

/**
 * When a message fails, bury it with the priority given to the constructor.
 *
 * @since 1.1.0
 */
final class BuryFailureStrategy implements FailureStrategy
{
    /**
     * The priority to assign to a buried job.
     *
     * @var int
     */
    private $priority;

    public function __construct($priority=null)
    {
        $this->priority = null === $priority ? PheanstalkInterface::DEFAULT_PRIORITY : intval($priority);
    }

    /**
     * {@inheritdoc}
     */
    public function fail(PheanstalkInterface $conn, PheanstalkEnvelope $env)
    {
        $conn->bury($env->getJob(), $this->priority);
    }
}
