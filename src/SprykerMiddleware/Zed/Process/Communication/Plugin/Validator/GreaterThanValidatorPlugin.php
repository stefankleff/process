<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerMiddleware\Zed\Process\Communication\Plugin\Validator;

use SprykerMiddleware\Zed\Process\Business\Validator\Validators\GreaterThanValidator;

class GreaterThanValidatorPlugin extends AbstractGenericValidatorPlugin
{
    public const NAME = 'GreaterThan';

    /**
     * @return string
     */
    public function getValidatorClassName(): string
    {
        return GreaterThanValidator::class;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }
}
