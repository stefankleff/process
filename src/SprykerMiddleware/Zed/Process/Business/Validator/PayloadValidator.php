<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerMiddleware\Zed\Process\Business\Validator;

use Generated\Shared\Transfer\ValidatorConfigTransfer;
use SprykerMiddleware\Shared\Logger\Logger\MiddlewareLoggerTrait;
use SprykerMiddleware\Zed\Process\Business\ArrayManager\ArrayManagerInterface;
use SprykerMiddleware\Zed\Process\Business\Exception\InvalidItemException;
use SprykerMiddleware\Zed\Process\Business\Validator\ValidationRuleSet\Resolver\ValidatorPluginResolverInterface;

class PayloadValidator implements PayloadValidatorInterface
{
    use MiddlewareLoggerTrait;

    protected const KEY_IS_VALID = 'isValid';
    protected const KEY_ITEM = 'item';
    protected const KEY_KEY = 'key';
    protected const KEY_OPTIONS = 'options';
    protected const KEY_VALIDATION_RULE = 'rule';
    protected const KEY_VALUE = 'value';
    protected const OPERATION = 'Validation';

    /**
     * @var \SprykerMiddleware\Zed\Process\Business\Validator\ValidationRuleSet\Resolver\ValidatorPluginResolverInterface
     */
    protected $validatorPluginResolver;

    /**
     * @var \SprykerMiddleware\Zed\Process\Business\ArrayManager\ArrayManagerInterface
     */
    protected $arrayManager;

    /**
     * @param \SprykerMiddleware\Zed\Process\Business\Validator\ValidationRuleSet\Resolver\ValidatorPluginResolverInterface $validatorPluginResolver
     * @param \SprykerMiddleware\Zed\Process\Business\ArrayManager\ArrayManagerInterface $arrayManager
     */
    public function __construct(
        ValidatorPluginResolverInterface $validatorPluginResolver,
        ArrayManagerInterface $arrayManager
    ) {
        $this->validatorPluginResolver = $validatorPluginResolver;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param array $payload
     * @param \Generated\Shared\Transfer\ValidatorConfigTransfer $validatorConfigTransfer
     *
     * @throws \SprykerMiddleware\Zed\Process\Business\Exception\InvalidItemException
     *
     * @return array
     */
    public function validate(array $payload, ValidatorConfigTransfer $validatorConfigTransfer): array
    {
        $isValid = true;
        foreach ($validatorConfigTransfer->getRules() as $key => $rules) {
            $isValid = $isValid && $this->validateKey($payload, $key, $rules);
        }

        if (!$isValid) {
            $this->getProcessLogger()->warning('Item is invalid. Processing of item is skipped', [
                static::KEY_ITEM => $payload,
            ]);
            throw new InvalidItemException("Item is invalid. Processing of item is skipped");
        }

        return $payload;
    }

    /**
     * @param array $payload
     * @param string $key
     * @param mixed $rules
     *
     * @return bool
     */
    protected function validateKey(array $payload, string $key, $rules): bool
    {
        if (!strstr($key, '*')) {
            return $this->validateByRuleSet($payload, $key, $rules);
        }

        return $this->validateNestedKeys($payload, $key, $rules);
    }

    /**
     * @param array $payload
     * @param string $key
     * @param mixed $rules
     *
     * @return bool
     */
    protected function validateByRuleSet(array $payload, string $key, $rules): bool
    {
        $isValid = true;
        if (!is_array($rules)) {
            $rules = [$rules];
        }
        foreach ($rules as $rule) {
            $isValid = $isValid && $this->validateByRule($payload, $key, $rule);
        }

        return $isValid;
    }

    /**
     * @param array $payload
     * @param string $key
     * @param mixed $rules
     *
     * @return bool
     */
    protected function validateNestedKeys(array $payload, string $key, $rules): bool
    {
        $isValid = true;
        $keys = $this->arrayManager->getAllNestedKeys($payload, $key);
        foreach ($keys as $key) {
            $isValid = $isValid && $this->validateByRuleSet($payload, $key, $rules);
        }

        return $isValid;
    }

    /**
     * @param array $payload
     * @param string $key
     * @param mixed $rule
     *
     * @return bool
     */
    protected function validateByRule(array $payload, string $key, $rule): bool
    {
        if (is_callable($rule)) {
            return $this->validateCallable($payload, $key, $rule);
        }

        return $this->validateValue($payload, $key, $rule);
    }

    /**
     * @param array $payload
     * @param string $key
     * @param callable $rule
     *
     * @return bool
     */
    protected function validateCallable(array $payload, string $key, callable $rule): bool
    {
        $inputValue = $this->arrayManager->getValueByKey($payload, $key);
        $isValid = $rule($inputValue, $key, $payload);

        $this->getProcessLogger()->debug(
            static::OPERATION,
            [
                static::KEY_KEY => $key,
                static::KEY_VALIDATION_RULE => $rule,
                static::KEY_VALUE => $inputValue,
                static::KEY_IS_VALID => $isValid,
            ]
        );

        return $isValid;
    }

    /**
     * @param array $payload
     * @param string $key
     * @param mixed $rule
     *
     * @return bool
     */
    protected function validateValue(array $payload, string $key, $rule): bool
    {
        if (!is_array($rule)) {
            $rule = [$rule];
        }
        $options = isset($rule[static::KEY_OPTIONS]) ? $rule[static::KEY_OPTIONS] : [];

        $validatorPlugin = $this->validatorPluginResolver
            ->getValidatorPluginByName(reset($rule));
        $inputValue = $this->arrayManager->getValueByKey($payload, $key);
        $isValid = $validatorPlugin->validate($inputValue, $payload, $key, $options);
        $this->getProcessLogger()->debug(
            static::OPERATION,
            [
                static::KEY_KEY => $key,
                static::KEY_VALIDATION_RULE => $rule,
                static::KEY_VALUE => $inputValue,
                static::KEY_IS_VALID => $isValid,
                static::KEY_OPTIONS => $options,
            ]
        );

        return $isValid;
    }
}
