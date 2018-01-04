<?php

namespace SprykerMiddleware\Zed\Process\Business\Mapper;

use Generated\Shared\Transfer\MapperConfigTransfer;
use Psr\Log\LoggerInterface;
use SprykerMiddleware\Shared\Process\ProcessConfig;
use SprykerMiddleware\Zed\Process\Business\ArrayManager\ArrayManagerInterface;

class Mapper implements MapperInterface
{
    const OPERATION = 'Mapping';
    const OPERATION_COPY_ORIGINAL_DATA = 'Copy original data';
    const OPERATION_MAP_ARRAY = 'Map array';
    const OPERATION_MAP_CALLABLE = 'Map callable';
    const OPERATION_MAP_KEY = 'Map key';

    const KEY_DATA = 'data';
    const KEY_NEW_KEY = 'new_key';
    const KEY_OLD_KEY = 'old_key';
    const KEY_OPERATION = 'operation';
    const KEY_STRATEGY = 'strategy';

    const OPTION_EXCEPT = 'except';
    const OPTION_ITEM_MAP = 'itemMap';
    const OPTION_ITEM_EXCEPT = 'itemExcept';
    /**
     * @var \Generated\Shared\Transfer\MapperConfigTransfer
     */
    protected $mapperConfigTransfer;

    /**
     * @var \SprykerMiddleware\Zed\Process\Business\ArrayManager\ArrayManagerInterface
     */
    protected $arrayManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Generated\Shared\Transfer\MapperConfigTransfer $mapperConfigTransfer
     * @param \SprykerMiddleware\Zed\Process\Business\ArrayManager\ArrayManagerInterface $arrayManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        MapperConfigTransfer $mapperConfigTransfer,
        ArrayManagerInterface $arrayManager,
        LoggerInterface $logger
    ) {
        $this->mapperConfigTransfer = $mapperConfigTransfer;
        $this->arrayManager = $arrayManager;
        $this->logger = $logger;
    }

    /**
     * @param array $payload
     *
     * @return array
     */
    public function map(array $payload): array
    {
        $result = $this->prepareResult($payload);
        foreach ($this->mapperConfigTransfer->getMap() as $key => $value) {
            $result = $this->mapByRule($result, $payload, $key, $value);
        }

        return $result;
    }

    /**
     * @param array $result
     * @param array $payload
     * @param string $key
     * @param mixed $value
     *
     * @return array
     */
    protected function mapByRule(array $result, array $payload, string $key, $value): array
    {
        if (is_callable($value)) {
            return $this->mapCallable($result, $payload, $key, $value);
        }
        if (is_array($value)) {
            return $this->mapArray($result, $payload, $key, $value);
        }
        if (is_string($value) || is_int($value)) {
            return $this->mapKey($result, $payload, $key, $value);
        }

        return $result;
    }

    /**
     * @param array $result
     * @param array $payload
     * @param string $key
     * @param callable $value
     *
     * @return array
     */
    protected function mapCallable(array $result, array $payload, string $key, callable $value): array
    {
        $mappedValue = $value($payload, $key);
        $this->logger->debug(static::OPERATION, [
            static::KEY_OPERATION => static::OPERATION_MAP_CALLABLE,
            static::KEY_NEW_KEY => $key,
            static::KEY_OLD_KEY => $value,
            static::KEY_DATA => $mappedValue,
        ]);

        return $this->arrayManager->putValue($result, $key, $mappedValue);
    }

    /**
     * @param array $result
     * @param array $payload
     * @param string $key
     * @param array $value
     *
     * @return array
     */
    protected function mapArray(array $result, array $payload, string $key, array $value): array
    {
        $originKey = reset($value);
        $originArray = $this->arrayManager->getValueByKey($payload, $originKey);
        $originArray = $this->filterArray($originArray, $value, static::OPTION_EXCEPT);
        $resultArray = $originArray;
        if (isset($value[static::OPTION_ITEM_MAP])) {
            $resultArray = [];
            $rules = $value[static::OPTION_ITEM_MAP];
            foreach ($originArray as $originItemKey => $item) {
                $resultItem = $this->prepareResult($item);
                $resultItem = $this->filterArray($resultItem, $value, static::OPTION_ITEM_EXCEPT);
                foreach ($rules as $itemKey => $itemValue) {
                    $resultItem = $this->mapByRule($resultItem, $item, $itemKey, $itemValue);
                }
                $resultArray[$originItemKey] = $resultItem;
            }
        }
        $this->logger->debug(static::OPERATION, [
            static::KEY_OPERATION => static::OPERATION_MAP_ARRAY,
            static::KEY_NEW_KEY => $key,
            static::KEY_OLD_KEY => $value,
            static::KEY_DATA => $resultArray,
        ]);

        return $this->arrayManager->putValue($result, $key, $resultArray);
    }

    /**
     * @param array $result
     * @param array $payload
     * @param string $key
     * @param string $value
     *
     * @return array
     */
    protected function mapKey(array $result, array $payload, string $key, string $value): array
    {
        $mappedValue = $this->arrayManager->getValueByKey($payload, $value);
        $this->logger->debug(static::OPERATION, [
            static::KEY_OPERATION => static::OPERATION_MAP_KEY,
            static::KEY_NEW_KEY => $key,
            static::KEY_OLD_KEY => $value,
            static::KEY_DATA => $mappedValue,
        ]);

        return $this->arrayManager->putValue($result, $key, $mappedValue);
    }

    /**
     * @param array $array
     * @param array $rule
     * @param string $exceptKey
     *
     * @return array
     */
    protected function filterArray(array $array, array $rule, string $exceptKey): array
    {
        if (!isset($rule[$exceptKey])) {
            return $array;
        }

        $exceptKeys = $rule[$exceptKey];
        if (!is_array($exceptKeys)) {
            $exceptKeys = [$exceptKeys];
        }

        return array_diff_key($array, array_flip($exceptKeys));
    }

    /**
     * @param array $payload
     *
     * @return array
     */
    protected function prepareResult(array $payload): array
    {
        if ($this->mapperConfigTransfer->getStrategy() === ProcessConfig::MAPPER_STRATEGY_COPY_UNKNOWN) {
            $this->logger->debug(static::OPERATION, [
                static::KEY_OPERATION => static::OPERATION_COPY_ORIGINAL_DATA,
                static::KEY_STRATEGY => $this->mapperConfigTransfer->getStrategy(),
            ]);
            return $payload;
        }

        return [];
    }
}
