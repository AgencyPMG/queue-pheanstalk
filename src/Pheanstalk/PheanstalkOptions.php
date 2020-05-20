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

/**
 * Abstraction around getting options for a given message -- this can change
 * how the message goes into the queue (eg priority, delay, etc)
 */
interface PheanstalkOptions
{
    const PRIORITY = 'priority';
    const DELAY = 'delay';
    const TTR = 'ttr';
    const RETRY_PRIORITY = 'retry-priority';
    const RETRY_TTR = 'retry-ttr';
    const FAIL_PRIORITY = 'fail-priority';
    const RELEASE_PRIORITY = 'release-priority';
    const RELEASE_DELAY = 'release-delay';
    const RESERVE_TIMEOUT = 'reserve-timeout';

    /**
     * Get the $optionName for the message.
     *
     * @param $optionName the option to retrieve
     * @param $message the message for which the option is being retrieved, may be
     *        null if retrieving an option for which there would not be a message
     * @throws MissingOption if the $optionName is not available
     * @return mixed but probably an int
     */
    public function getMessageOption(string $optionName, object $message);

    /**
     * Get a global $optionName
     *
     * @param $optionName the option to retrieve
     * @throws MissingOption if the $optionName is not available
     * @return mixed but probably an int
     */
    public function getGlobalOption(string $optionName);
}
