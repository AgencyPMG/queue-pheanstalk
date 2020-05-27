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

use PMG\Queue\Exception\DriverError;

/**
 * Thrown when a PheanstalkOptions implementation can't find the option that
 * was asked of it.
 */
final class MissingOption extends \RuntimeException implements DriverError
{
    public static function fromName(string $optionName) : self
    {
        return new self(sprintf('missing option "%s"', $optionName));
    }
}
