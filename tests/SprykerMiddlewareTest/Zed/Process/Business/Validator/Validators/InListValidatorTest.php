<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerMiddlewareTest\Zed\Process\Business\Validator\Validators;

use Codeception\Test\Unit;
use SprykerMiddleware\Zed\Process\Business\Validator\Validators\InListValidator;

/**
 * Auto-generated group annotations
 * @group SprykerMiddlewareTest
 * @group Zed
 * @group Process
 * @group Business
 * @group Validator
 * @group Validators
 * @group InListValidatorTest
 */
class InListValidatorTest extends Unit
{
    /**
     * @return void
     */
    public function testValidation()
    {
        $validator = new InListValidator();
        $validator->setOptions(['values' => [12, 13, 14]]);

        $value = 13;
        $this->assertTrue($validator->validate($value, []));

        $value = 34;
        $this->assertFalse($validator->validate($value, []));
    }
}
