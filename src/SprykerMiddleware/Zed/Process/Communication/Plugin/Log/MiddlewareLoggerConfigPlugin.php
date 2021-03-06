<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerMiddleware\Zed\Process\Communication\Plugin\Log;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerMiddleware\Zed\Process\Dependency\Plugin\Log\MiddlewareLoggerConfigPluginInterface;

/**
 * @method \SprykerMiddleware\Zed\Process\Business\ProcessFacadeInterface getFacade()
 * @method \SprykerMiddleware\Zed\Process\Communication\ProcessCommunicationFactory getFactory()
 */
class MiddlewareLoggerConfigPlugin extends AbstractPlugin implements MiddlewareLoggerConfigPluginInterface
{
    protected const CHANNEL_NAME = 'SprykerMiddleware';
    protected const PLUGIN_NAME = 'MiddlewareLoggerConfigPlugin';

    /**
     * @var \Monolog\Handler\AbstractHandler[]
     */
    protected $handlers;

    /**
     * @api
     *
     * @return string
     */
    public function getChannelName(): string
    {
        return static::CHANNEL_NAME;
    }

    /**
     * @api
     *
     * @return \Monolog\Handler\AbstractHandler[]
     */
    public function getHandlers(): array
    {
        if ($this->handlers) {
            return $this->handlers;
        }

        return $this->getFactory()->getMiddlewareLogHandlers();
    }

    /**
     * @api
     *
     * @return callable[]
     */
    public function getProcessors(): array
    {
        return $this->getFactory()->getMiddlewareLogProcessors();
    }

    /**
     * Sets minimum logging level at which all handlers will be triggered.
     *
     * @api
     *
     * @param int|string $level Level or level name
     *
     * @return void
     */
    public function changeLogLevel($level): void
    {
        $this->handlers = $this->getFactory()->getMiddlewareLogHandlers();
        foreach ($this->handlers as $handler) {
            $handler->setLevel($level);
        }
    }

    /**
     * @api
     *
     * @return string
     */
    public function getName(): string
    {
        return static::PLUGIN_NAME;
    }
}
