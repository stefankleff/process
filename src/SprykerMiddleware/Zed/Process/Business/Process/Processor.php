<?php
namespace SprykerMiddleware\Zed\Process\Business\Process;

use Iterator;
use Psr\Log\LoggerInterface;
use SprykerMiddleware\Zed\Process\Business\Aggregator\AggregatorInterface;
use SprykerMiddleware\Zed\Process\Business\Exception\TolerableProcessException;
use SprykerMiddleware\Zed\Process\Business\Pipeline\PipelineInterface;

class Processor implements ProcessorInterface
{
    /**
     * @var \Iterator
     */
    protected $iterator;

    /**
     * @var \SprykerMiddleware\Zed\Process\Business\Aggregator\AggregatorInterface
     */
    protected $aggregator;

    /**
     * @var \SprykerMiddleware\Zed\Process\Business\Pipeline\PipelineInterface
     */
    protected $pipeline;

    /**
     * @var \SprykerMiddleware\Zed\Process\Business\Process\Hook\PreProcessorHookPluginInterface[]
     */
    protected $preProcessStack;

    /**
     * @var \SprykerMiddleware\Zed\Process\Business\Process\Hook\PostProcessorHookPluginInterface[]
     */
    protected $postProcessStack;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Iterator $iterator
     * @param \SprykerMiddleware\Zed\Process\Business\Pipeline\PipelineInterface $pipeline
     * @param \SprykerMiddleware\Zed\Process\Business\Aggregator\AggregatorInterface $aggregator
     * @param \SprykerMiddleware\Zed\Process\Business\Process\Hook\PreProcessorHookPluginInterface[] $preProcessStack
     * @param \SprykerMiddleware\Zed\Process\Business\Process\Hook\PostProcessorHookPluginInterface[] $postProcessStack
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Iterator $iterator,
        PipelineInterface $pipeline,
        AggregatorInterface $aggregator,
        array $preProcessStack,
        array $postProcessStack,
        LoggerInterface $logger
    ) {
        $this->iterator = $iterator;
        $this->pipeline = $pipeline;
        $this->aggregator = $aggregator;
        $this->preProcessStack = $preProcessStack;
        $this->postProcessStack = $postProcessStack;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function process()
    {
        $this->preProcess();
        $this->logger->info('Middleware process is started.', ['process' => $this]);
        $counter = 0;
        foreach ($this->iterator as $item) {
            $this->logger->info('Start processing of item', [
                'item' => $item,
                'itemKey' => $this->iterator->key(),
                'itemNo' => $counter++,
            ]);
            try {
                $this->aggregator->accept(
                    $this->pipeline->process($item)
                );
            } catch (TolerableProcessException $exception) {
                $this->logger->error('Experienced tolerable process error in ' . $exception->getFile());
            }
        }
        $this->aggregator->flush();
        $this->logger->info('Middleware process is finished.');
        $this->postProcess();
    }

    /**
     * @return void
     */
    public function preProcess()
    {
        foreach ($this->preProcessStack as $preProcessor) {
            $preProcessor->process();
        }
    }

    /**
     * @return void
     */
    public function postProcess()
    {
        foreach ($this->postProcessStack as $postProcessor) {
            $postProcessor->process();
        }
    }
}
