<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerMiddleware\Zed\Process\Business;

use Generated\Shared\Transfer\MapperConfigTransfer;
use Generated\Shared\Transfer\ProcessSettingsTransfer;
use Generated\Shared\Transfer\TranslatorConfigTransfer;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use SprykerMiddleware\Zed\Process\Business\ArrayManager\ArrayManager;
use SprykerMiddleware\Zed\Process\Business\ArrayManager\ArrayManagerInterface;
use SprykerMiddleware\Zed\Process\Business\Mapper\Mapper;
use SprykerMiddleware\Zed\Process\Business\Mapper\MapperInterface;
use SprykerMiddleware\Zed\Process\Business\Pipeline\Pipeline;
use SprykerMiddleware\Zed\Process\Business\Pipeline\PipelineInterface;
use SprykerMiddleware\Zed\Process\Business\Pipeline\Processor\FingersCrossedProcessor;
use SprykerMiddleware\Zed\Process\Business\Pipeline\Processor\PipelineProcessorInterface;
use SprykerMiddleware\Zed\Process\Business\Pipeline\Stage\StageListBuilder;
use SprykerMiddleware\Zed\Process\Business\Pipeline\Stage\StageListBuilderInterface;
use SprykerMiddleware\Zed\Process\Business\PluginResolver\ProcessPluginResolver;
use SprykerMiddleware\Zed\Process\Business\PluginResolver\ProcessPluginResolverInterface;
use SprykerMiddleware\Zed\Process\Business\Process\Processor;
use SprykerMiddleware\Zed\Process\Business\Process\ProcessorInterface;
use SprykerMiddleware\Zed\Process\Business\Translator\Translator;
use SprykerMiddleware\Zed\Process\Business\Translator\TranslatorFunction\TranslatorFunctionPluginResolver;
use SprykerMiddleware\Zed\Process\Business\Translator\TranslatorFunction\TranslatorFunctionPluginResolverInterface;
use SprykerMiddleware\Zed\Process\Business\Translator\TranslatorInterface;
use SprykerMiddleware\Zed\Process\ProcessDependencyProvider;

/**
 * @method \SprykerMiddleware\Zed\Process\ProcessConfig getConfig()
 */
class ProcessBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @param \Generated\Shared\Transfer\ProcessSettingsTransfer $processSettingsTransfer
     *
     * @return \SprykerMiddleware\Zed\Process\Business\Process\ProcessorInterface
     */
    public function createProcessor(
        ProcessSettingsTransfer $processSettingsTransfer
    ): ProcessorInterface {
        return new Processor(
            $processSettingsTransfer,
            $this->createPipeline($processSettingsTransfer),
            $this->createProcessPluginResolver()
        );
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Business\PluginResolver\ProcessPluginResolverInterface
     */
    public function createProcessPluginResolver(): ProcessPluginResolverInterface
    {
        return new ProcessPluginResolver($this->getProcessesPluginsStack());
    }

    /**
     * @param \Generated\Shared\Transfer\MapperConfigTransfer $mapperConfigTransfer
     *
     * @return \SprykerMiddleware\Zed\Process\Business\Mapper\MapperInterface
     */
    public function createMapper(MapperConfigTransfer $mapperConfigTransfer): MapperInterface
    {
        return new Mapper(
            $mapperConfigTransfer,
            $this->createArrayManager()
        );
    }

    /**
     * @param \Generated\Shared\Transfer\TranslatorConfigTransfer $translatorConfigTransfer
     *
     * @return \SprykerMiddleware\Zed\Process\Business\Translator\TranslatorInterface
     */
    public function createTranslator(TranslatorConfigTransfer $translatorConfigTransfer): TranslatorInterface
    {
        return new Translator(
            $translatorConfigTransfer,
            $this->createTranslatorFunctionResolver(),
            $this->createArrayManager()
        );
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Business\ArrayManager\ArrayManagerInterface
     */
    public function createArrayManager(): ArrayManagerInterface
    {
        return new ArrayManager();
    }

    /**
     * @param \Generated\Shared\Transfer\ProcessSettingsTransfer $processSettingsTransfer
     *
     * @return \SprykerMiddleware\Zed\Process\Business\Pipeline\PipelineInterface
     */
    protected function createPipeline(
        ProcessSettingsTransfer $processSettingsTransfer
    ): PipelineInterface {
        return new Pipeline(
            $this->createPipelineProcessor(),
            $this->getStages($processSettingsTransfer)
        );
    }

    /**
     * @param \Generated\Shared\Transfer\ProcessSettingsTransfer $processSettingsTransfer
     *
     * @return \SprykerMiddleware\Zed\Process\Business\Pipeline\Stage\StageInterface[]
     */
    protected function getStages(
        ProcessSettingsTransfer $processSettingsTransfer
    ): array {
        return $this->createStageListBuilder()
            ->buildStageList($processSettingsTransfer);
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Business\Pipeline\Stage\StageListBuilderInterface
     */
    protected function createStageListBuilder(): StageListBuilderInterface
    {
        return new StageListBuilder(
            $this->createProcessPluginResolver()
        );
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Business\Pipeline\Processor\PipelineProcessorInterface
     */
    protected function createPipelineProcessor(): PipelineProcessorInterface
    {
        return new FingersCrossedProcessor();
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Business\Translator\TranslatorFunction\TranslatorFunctionPluginResolverInterface
     */
    protected function createTranslatorFunctionResolver(): TranslatorFunctionPluginResolverInterface
    {
        return new TranslatorFunctionPluginResolver($this->getTranslatorFunctionsStack());
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Dependency\Plugin\Log\MiddlewareLoggerConfigPluginInterface
     */
    protected function getDefaultLoggerConfigPlugin()
    {
        return $this->getProvidedDependency(ProcessDependencyProvider::MIDDLEWARE_DEFAULT_LOG_CONFIG_PLUGIN);
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Dependency\Plugin\Log\MiddlewareLoggerConfigPluginInterface
     */
    protected function getProcessesPluginsStack()
    {
        return $this->getProvidedDependency(ProcessDependencyProvider::MIDDLEWARE_PROCESSES);
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Dependency\Plugin\TranslatorFunction\TranslatorFunctionPluginInterface[]
     */
    protected function getTranslatorFunctionsStack()
    {
        return $this->getProvidedDependency(ProcessDependencyProvider::MIDDLEWARE_TRANSLATOR_FUNCTIONS);
    }
}
