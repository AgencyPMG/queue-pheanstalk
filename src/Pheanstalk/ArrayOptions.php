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
 * An options implementation backed by an array passed to the constructor.
 * This mimics the behavior that already existed in 5.0
 */
final class ArrayOptions implements PheanstalkOptions
{
    /**
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        $this->options = array_replace([
            self::PRIORITY => PheanstalkInterface::DEFAULT_PRIORITY,
            self::DELAY => PheanstalkInterface::DEFAULT_DELAY,
            self::TTR => PheanstalkInterface::DEFAULT_TTR,
            self::RETRY_PRIORITY => PheanstalkInterface::DEFAULT_PRIORITY,
            self::RETRY_TTR => PheanstalkInterface::DEFAULT_TTR,
            self::FAIL_PRIORITY => PheanstalkInterface::DEFAULT_PRIORITY,
            self::RELEASE_PRIORITY => PheanstalkInterface::DEFAULT_PRIORITY,
            self::RELEASE_DELAY => PheanstalkInterface::DEFAULT_DELAY,
            self::RESERVE_TIMEOUT => 10,
        ], $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageOption(string $optionName, object $message)
    {
        return $this->get($optionName);
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobalOption(string $optionName)
    {
        return $this->get($optionName);
    }

    private function get(string $optionName)
    {
        if (!isset($this->options[$optionName])) {
            throw MissingOption::fromName($optionName);
        }

        return $this->options[$optionName];
    }
}
