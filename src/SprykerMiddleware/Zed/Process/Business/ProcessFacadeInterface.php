<?php

namespace SprykerMiddleware\Zed\Process\Business;

use Generated\Shared\Transfer\MapperConfigTransfer;
use Generated\Shared\Transfer\ProcessSettingsTransfer;
use Generated\Shared\Transfer\TranslatorConfigTransfer;
use Psr\Log\LoggerInterface;

interface ProcessFacadeInterface
{
    /**
     * @param \Generated\Shared\Transfer\ProcessSettingsTransfer $processSettingsTransfer $processSettingsTransfer
     * @param resource $inStream
     * @param resource $outStream
     *
     * @return void
     */
    public function process(ProcessSettingsTransfer $processSettingsTransfer, $inStream, $outStream): void;

    /**
     * @param array $payload
     * @param \Generated\Shared\Transfer\MapperConfigTransfer $mapperConfigTransfer
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return array
     */
    public function map(array $payload, MapperConfigTransfer $mapperConfigTransfer, LoggerInterface $logger): array;

    /**
     * @param array $payload
     * @param \Generated\Shared\Transfer\TranslatorConfigTransfer $translatorConfigTransfer
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return array
     */
    public function translate(array $payload, TranslatorConfigTransfer $translatorConfigTransfer, LoggerInterface $logger): array;

    /**
     * @param array $payload
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return array
     */
    public function readJson($payload, LoggerInterface $logger): array;

    /**
     * @param resource $outStream
     * @param array $payload
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return array
     */
    public function writeJson($outStream, $payload, $logger);
}
