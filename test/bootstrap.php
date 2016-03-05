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

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('PMG\\Queue\\Driver\\', __DIR__);
