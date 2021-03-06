<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerMiddleware\Zed\Process\Business\Translator\TranslatorFunction;

use SprykerMiddleware\Zed\Process\Business\Exception\WrongTypeValueTranslatorException;

class ArrayToString extends AbstractTranslatorFunction
{
    public const OPTION_GLUE = 'glue';

    /**
     * @var array
     */
    protected $requiredOptions = [
        self::OPTION_GLUE,
    ];

    /**
     * @param array $value
     * @param array $payload
     *
     * @throws \SprykerMiddleware\Zed\Process\Business\Exception\WrongTypeValueTranslatorException
     *
     * @return string
     */
    public function translate($value, array $payload): string
    {
        if (!is_array($value)) {
            throw new WrongTypeValueTranslatorException(static::class, $this->key, 'array', $value);
        }

        return implode($this->options[self::OPTION_GLUE], $value);
    }
}
