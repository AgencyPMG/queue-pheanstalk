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
 * Used to wrap exceptions from the pheanstalk library.
 *
 * @since   2.0
 */
final class PheanstalkError extends \RuntimeException implements DriverError
{
    public static function fromException(\Pheanstalk\Exception $e)
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }
}
