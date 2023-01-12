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

namespace PMG\Queue\Driver\Pheanstalk;

use PMG\Queue\Driver\PheanstalkTestCase;

class ArrayOptionsTest extends PheanstalkTestCase
{
    private ArrayOptions $options;

    public function testGetMessageOptionErrorsWhenGivenAnInvalidOptionName()
    {
        $this->expectException(MissingOption::class);

        $this->options->getMessageOption(__METHOD__, new class() {});
    }

    public function testGetMessageOptionReturnsOptionValueWhenOptionExists()
    {
        $option = $this->options->getMessageOption('test', new class() {});

        $this->assertSame(123, $option);
    }

    public function testGetGlobalOptionErrorsWhenGivenAnInvalidOptionName()
    {
        $this->expectException(MissingOption::class);

        $this->options->getGlobalOption(__METHOD__);
    }

    public function testGetGlobalOptionReturnsOptionValueWhenOptionExists()
    {
        $option = $this->options->getGlobalOption('test');

        $this->assertSame(123, $option);
    }

    protected function setUp() : void
    {
        $this->options = new ArrayOptions([
            'test' => 123,
        ]);
    }
}
