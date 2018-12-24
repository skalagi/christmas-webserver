<?php

namespace Syntax\Service;

use React\EventLoop\LoopInterface;
use Syntax\ChristmasContainer;
use Syntax\Exception\QueueException;
use Syntax\Model\ChangeColor;
use Syntax\Model\ChangeState;
use Syntax\Service\Executor\AbstractExecutor;

class Queue
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var int
     */
    private $interval = .3;

    /**
     * @var bool
     */
    private $_is_in_queue_call_now = false;

    /**
     * Queue constructor.
     * @param LoopInterface $loop
     * @param $interval
     */
    public function __construct(LoopInterface $loop, $interval)
    {
        $this->loop = $loop;
        $this->interval = $interval;

        $this->loop->addPeriodicTimer($this->interval, [$this, '_queueCall']);
    }

    /**
     * @param $item
     * @return Queue
     * @throws \Exception
     */
    public function push($item)
    {
        $this->items[] = $item;
        ChristmasContainer::getLogger()->addLog(Logger::QUEUE, 'Added queue item: '.get_class($item), null, null);
        return $this;
    }

    /**
     * @return array|ChangeState[]|ChangeColor[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @throws \Exception
     */
    public function _queueCall()
    {
        if(!$this->items || $this->_is_in_queue_call_now) {
            return;
        }

        $this->_is_in_queue_call_now = true;

        $item = array_shift($this->items);
        $service = $this->_getExecutorService($item);
        ChristmasContainer::getLogger()->addLog(Logger::QUEUE, 'Executing queue item: '.get_class($item), null, null);
        $service->execute($item);

        $this->_is_in_queue_call_now = false;
    }

    /**
     * @param $item
     * @return AbstractExecutor|object
     * @throws QueueException
     * @throws \Exception
     */
    private function _getExecutorService($item)
    {
        switch(get_class($item)) {
            case ChangeColor::class:
                return ChristmasContainer::getInstance()->get('queue.color_executor');

            case ChangeState::class:
                return ChristmasContainer::getInstance()->get('queue.state_executor');
        }

        throw new QueueException(sprintf('Not found executor service for item: '.get_class($item)));
    }
}