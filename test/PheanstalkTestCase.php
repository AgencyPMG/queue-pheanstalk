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

use Pheanstalk\Pheanstalk;
use Pheanstalk\Contract\PheanstalkInterface;

abstract class PheanstalkTestCase extends \PHPUnit\Framework\TestCase
{
    protected static function createConnection() : PheanstalkInterface
    {
        return  Pheanstalk::create(
            self::getBeanstalkdHost(),
            self::getBeanstalkdPort()
        );
    }

    protected static function getBeanstalkdHost() : string
    {
        return getenv('PMG_QUEUE_HOST') ?: 'localhost';
    }

    protected static function getBeanstalkdPort() : int
    {
        return intval(getenv('PMG_QUEUE_PORT') ?: PheanstalkInterface::DEFAULT_PORT);
    }
}
