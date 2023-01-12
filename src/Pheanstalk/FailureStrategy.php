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
 * Used by the Pheanstalk driver to handle message failures in a pluggable way.
 *
 * @since 1.1.0
 */
interface FailureStrategy
{
    public function fail(PheanstalkInterface $conn, PheanstalkEnvelope $env) : void;
}
