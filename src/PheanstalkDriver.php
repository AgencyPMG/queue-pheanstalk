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

namespace PMG\Queue\Driver;

use Pheanstalk\Job;
use Pheanstalk\Contract\PheanstalkInterface;
use PMG\Queue\DefaultEnvelope;
use PMG\Queue\Envelope;
use PMG\Queue\Message;
use PMG\Queue\Exception\InvalidArgumentException;
use PMG\Queue\Exception\InvalidEnvelope;
use PMG\Queue\Serializer\Serializer;
use PMG\Queue\Driver\Pheanstalk\ArrayOptions;
use PMG\Queue\Driver\Pheanstalk\FailureStrategy;
use PMG\Queue\Driver\Pheanstalk\PheanstalkEnvelope;
use PMG\Queue\Driver\Pheanstalk\PheanstalkError;
use PMG\Queue\Driver\Pheanstalk\PheanstalkOptions;

/**
 * A driver implementatio backed by Pheanstalk & Beanstalkd.
 *
 * The options array takes a set of values related to how the messages are
 * put into beanstalkd.
 *
 * @since   2.0
 */
final class PheanstalkDriver extends AbstractPersistanceDriver
{
    private PheanstalkInterface $conn;

    private PheanstalkOptions $options;

    private FailureStrategy $failure;

    public function __construct(
        PheanstalkInterface $conn,
        Serializer $serializer,
        ?PheanstalkOptions $options=null,
        ?FailureStrategy $failure=null,
    ) {
        parent::__construct($serializer);
        $this->conn = $conn;
        $this->options = $options ?? new ArrayOptions([]);
        $this->failure = $failure ?? new Pheanstalk\BuryFailureStrategy($this->options);
    }

    /**
     * {@inheritdoc}
     */
    public static function allowedClasses()
    {
        $cls = parent::allowedClasses();
        $cls[] = PheanstalkEnvelope::class;

        return $cls;
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(string $queueName, object $message) : Envelope
    {
        $env = new DefaultEnvelope($message);
        $data = $this->serialize($env);

        try {
            $job = $this->conn->useTube($queueName)->put(
                $data,
                $this->options->getMessageOption(PheanstalkOptions::PRIORITY, $message),
                $this->options->getMessageOption(PheanstalkOptions::DELAY, $message),
                $this->options->getMessageOption(PheanstalkOptions::TTR, $message),
            );
        } catch (\Pheanstalk\Exception $e) {
            throw PheanstalkError::fromException($e);
        }

        return new PheanstalkEnvelope($job, $env);
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue(string $queueName) : ?Envelope
    {
        $job = null;
        try {
            $job = $this->conn->watchOnly($queueName)->reserveWithTimeout(
                $this->options->getGlobalOption(PheanstalkOptions::RESERVE_TIMEOUT)
            );
        } catch (\Pheanstalk\Exception $e) {
            throw PheanstalkError::fromException($e);
        }

        return $job ? new PheanstalkEnvelope($job, $this->unserialize($job->getData())) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function ack(string $queueName, Envelope $env) : void
    {
        try {
            $this->conn->delete($this->assurePheanstalkEnvelope($env)->getJob());
        } catch (\Pheanstalk\Exception $e) {
            throw PheanstalkError::fromException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function retry(string $queueName, Envelope $env) : Envelope
    {
        $pheanstalkEnv = $this->assurePheanstalkEnvelope($env);
        $realEnvelope = $pheanstalkEnv->getWrappedEnvelope();
        $data = $this->serialize($realEnvelope);

        // since we need to update the job payload here, we have to delete
        // it and re-add it manually. This isn't transational, so there's
        // a (very real) possiblity of data loss.
        try {
            $job = $this->conn->useTube($queueName)->put(
                $data,
                $this->options->getMessageOption(PheanstalkOptions::RETRY_PRIORITY, $realEnvelope->unwrap()),
                $pheanstalkEnv->delay(),
                $this->options->getMessageOption(PheanstalkOptions::RETRY_TTR, $realEnvelope->unwrap()),
            );
            $this->conn->delete($pheanstalkEnv->getJob());
        } catch (\Pheanstalk\Exception $e) {
            throw PheanstalkError::fromException($e);
        }

        return new PheanstalkEnvelope($job, $realEnvelope);
    }

    /**
     * {@inheritdoc}
     */
    public function fail(string $queueName, Envelope $env) : void
    {
        try {
            $this->failure->fail($this->conn, $this->assurePheanstalkEnvelope($env));
        } catch (\Pheanstalk\Exception $e) {
            throw PheanstalkError::fromException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function release(string $queueName, Envelope $env) : void
    {
        $env = $this->assurePheanstalkEnvelope($env);

        try {
            $this->conn->release(
                $env->getJob(),
                $this->options->getMessageOption(PheanstalkOptions::RELEASE_PRIORITY, $env->unwrap()),
                $env->delay(),
                $this->options->getMessageOption(PheanstalkOptions::RELEASE_DELAY, $env->unwrap())
            );
        } catch (\Pheanstalk\Exception $e) {
            throw PheanstalkError::fromException($e);
        }
    }

    private function assurePheanstalkEnvelope(Envelope $env) : PheanstalkEnvelope
    {
        if (!$env instanceof PheanstalkEnvelope) {
            throw new InvalidEnvelope(sprintf(
                '%s requires that envelopes be instances of "%s", got "%s"',
                __CLASS__,
                PheanstalkEnvelope::class,
                get_class($env)
            ));
        }

        return $env;
    }
}
