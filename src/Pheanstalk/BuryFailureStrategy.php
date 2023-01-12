<?php declare(strict_types=1);
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
    private PheanstalkOptions $options;

    public function __construct(?PheanstalkOptions $options=null)
    {
        $this->options = $options ?? new ArrayOptions([
            PheanstalkOptions::FAIL_PRIORITY => PheanstalkInterface::DEFAULT_PRIORITY,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function fail(PheanstalkInterface $conn, PheanstalkEnvelope $env)
    {
        $conn->bury($env->getJob(), $this->options->getMessageOption(
            PheanstalkOptions::FAIL_PRIORITY,
            $env->unwrap()
        ));
    }
}
