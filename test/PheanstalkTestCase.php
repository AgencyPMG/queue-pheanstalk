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

abstract class PheanstalkTestCase extends \PHPUnit\Framework\TestCase
{
    protected static function createConnection()
    {
        $host = getenv('PMG_QUEUE_HOST') ?: 'localhost';
        $port = intval(getenv('PMG_QUEUE_PORT') ?: \Pheanstalk\PheanstalkInterface::DEFAULT_PORT);

        return  new \Pheanstalk\Pheanstalk($host, $port);
    }
}
