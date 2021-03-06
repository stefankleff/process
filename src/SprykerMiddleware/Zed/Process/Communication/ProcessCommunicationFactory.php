<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerMiddleware\Zed\Process\Communication;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use SprykerMiddleware\Service\Process\ProcessServiceInterface;
use SprykerMiddleware\Zed\Process\Business\Iterator\IteratorFactory;
use SprykerMiddleware\Zed\Process\Business\Iterator\IteratorFactoryInterface;
use SprykerMiddleware\Zed\Process\Business\Stream\StreamFactory;
use SprykerMiddleware\Zed\Process\Business\Stream\StreamFactoryInterface;
use SprykerMiddleware\Zed\Process\Business\Translator\TranslatorFunction\TranslatorFunctionFactory;
use SprykerMiddleware\Zed\Process\Business\Translator\TranslatorFunction\TranslatorFunctionFactoryInterface;
use SprykerMiddleware\Zed\Process\Business\Validator\Factory\ValidatorFactory;
use SprykerMiddleware\Zed\Process\Business\Validator\Factory\ValidatorFactoryInterface;
use SprykerMiddleware\Zed\Process\Dependency\External\ProcessToSymfonyDecoderAdapterInterface;
use SprykerMiddleware\Zed\Process\Dependency\External\ProcessToSymfonyEncoderAdapterInterface;
use SprykerMiddleware\Zed\Process\ProcessDependencyProvider;

/**
 * @method \SprykerMiddleware\Zed\Process\ProcessConfig getConfig()
 * @SuppressWarnings(FactoryMethodReturnInterfaceRule)
 */
class ProcessCommunicationFactory extends AbstractCommunicationFactory
{
    /**
     * @return \SprykerMiddleware\Zed\Process\Business\Iterator\IteratorFactoryInterface
     */
    public function createIteratorFactory(): IteratorFactoryInterface
    {
        return new IteratorFactory($this->createStreamFactory());
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Business\Stream\StreamFactoryInterface
     */
    public function createStreamFactory(): StreamFactoryInterface
    {
        return new StreamFactory();
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Business\Translator\TranslatorFunction\TranslatorFunctionFactoryInterface
     */
    public function createTranslatorFunctionFactory(): TranslatorFunctionFactoryInterface
    {
        return new TranslatorFunctionFactory();
    }

    /**
     * @return \Monolog\Processor\IntrospectionProcessor
     */
    public function createIntrospectionProcessor(): IntrospectionProcessor
    {
        return new IntrospectionProcessor();
    }

    /**
     * @return \SprykerMiddleware\Service\Process\ProcessServiceInterface
     */
    public function getProcessService(): ProcessServiceInterface
    {
        return $this->getProvidedDependency(ProcessDependencyProvider::SERVICE_PROCESS);
    }

    /**
     * @return \Monolog\Handler\AbstractHandler[]
     */
    public function getMiddlewareLogHandlers(): array
    {
        return $this->getProvidedDependency(ProcessDependencyProvider::MIDDLEWARE_LOG_HANDLERS);
    }

    /**
     * @return callable[]
     */
    public function getMiddlewareLogProcessors(): array
    {
        return $this->getProvidedDependency(ProcessDependencyProvider::MIDDLEWARE_LOG_PROCESSORS);
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Dependency\Plugin\Configuration\ProcessConfigurationPluginInterface[]
     */
    public function getProcessesPlugins(): array
    {
        return $this->getProvidedDependency(ProcessDependencyProvider::MIDDLEWARE_PROCESSES);
    }

    /**
     * @return \Monolog\Handler\AbstractHandler
     */
    public function createStdErrStreamHandler(): AbstractHandler
    {
        $streamHandler = new StreamHandler(
            'php://stderr',
            Logger::DEBUG
        );
        $formatter = $this->createLogstashFormatter();
        $streamHandler->setFormatter($formatter);

        return $streamHandler;
    }

    /**
     * @return \Monolog\Formatter\FormatterInterface
     */
    public function createLogstashFormatter(): FormatterInterface
    {
        return new LogstashFormatter(APPLICATION);
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Dependency\Plugin\TranslatorFunction\TranslatorFunctionPluginInterface[]
     */
    public function getTranslatorFunctionsPlugins(): array
    {
        return $this->getProvidedDependency(ProcessDependencyProvider::MIDDLEWARE_TRANSLATOR_FUNCTIONS);
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Dependency\Plugin\Validator\ValidatorPluginInterface[]
     */
    public function getValidatorPlugins()
    {
        return $this->getProvidedDependency(ProcessDependencyProvider::MIDDLEWARE_VALIDATORS);
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Business\Validator\Factory\ValidatorFactoryInterface;
     */
    public function createValidatorFactory(): ValidatorFactoryInterface
    {
        return new ValidatorFactory();
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Dependency\External\ProcessToSymfonyDecoderAdapterInterface
     */
    public function getDecoder(): ProcessToSymfonyDecoderAdapterInterface
    {
        return $this->getProvidedDependency(ProcessDependencyProvider::DECODER);
    }

    /**
     * @return \SprykerMiddleware\Zed\Process\Dependency\External\ProcessToSymfonyEncoderAdapterInterface
     */
    public function getEncoder(): ProcessToSymfonyEncoderAdapterInterface
    {
        return $this->getProvidedDependency(ProcessDependencyProvider::ENCODER);
    }
}
