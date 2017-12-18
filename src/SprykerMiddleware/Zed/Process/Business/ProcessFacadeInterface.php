<?php

namespace SprykerMiddleware\Zed\Process\Business;

use Psr\Log\LoggerInterface;
use SprykerMiddleware\Zed\Process\Business\Mapper\Map\MapInterface;

interface ProcessFacadeInterface
{
    /**
     * @param array $payload
     * @param \SprykerMiddleware\Zed\Process\Business\Mapper\Map\MapInterface $map
     *
     * @return array
     */
    public function map(array $payload, MapInterface $map);

    /**
     * @param array $payload
     * @param array $dictionary
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return mixed
     */
    public function translate(array $payload, array $dictionary, LoggerInterface $logger);

    /**
     * @param array $payload
     * @param string $writerName
     * @param string $destination
     *
     * @return array
     */
    public function write(array $payload, string $writerName, string $destination);
}
