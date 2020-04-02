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
 * When a message fails, delete it. Use this if there are other accoutability
 * measures in place around your queue.
 *
 * @since 1.1.0
 */
final class DeleteFailureStrategy implements FailureStrategy
{
    /**
     * {@inheritdoc}
     */
    public function fail(PheanstalkInterface $conn, PheanstalkEnvelope $env)
    {
        $conn->delete($env->getJob());
    }
}
