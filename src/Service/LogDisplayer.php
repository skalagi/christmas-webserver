<?php

namespace Syntax\Service;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Syntax\Model\Application\LogEntity;
use Syntax\Model\Application\LogEvents;

class LogDisplayer
{
    /**
     * @var ConsoleOutput
     */
    private $std;

    /**
     * @var bool
     */
    private $displayingStarted = false;

    /**
     * LogDisplayer constructor.
     * @param ConsoleOutput $output
     */
    public function __construct(ConsoleOutput $output)
    {
        $this->std = $output;
    }

    /**
     * @param LogEntity $log
     */
    public function renderLog(LogEntity $log)
    {
        if($this->displayingStarted) {
            $this->doRenderLog($log);
        }
    }

    /**
     * Render log entry
     * @param LogEntity $log
     */
    private function doRenderLog(LogEntity $log)
    {
        $this->std->writeln('['.$log->createdTime->format('d.m.y H:i:s').'] '.LogEvents::getLabel($log->name));
        if($log->data && is_array($log->data)) {
            $table = new Table($this->std);
            $table->setHeaders($this->createTableHeaders($log->data));
            $table->addRow(
                $this->convertBooleansInData(array_values($log->data))
            );
            $table->render();
        }

        $this->std->write(PHP_EOL);
    }

    /**
     * Start displaying log table
     * @param array $logs
     */
    public function startDisplaying(array $logs)
    {
        foreach(array_reverse($logs) as $log) {
            $this->doRenderLog($log);
        }

        $this->displayingStarted = true;
    }

    /**
     * @param array $data
     * @return array
     */
    private function createTableHeaders(array $data)
    {
        $keys = [];
        foreach(array_keys($data) as $key) {
            $keys[] = strtoupper($key);
        }
        return $keys;
    }

    /**
     * @param array $data
     * @return array
     */
    private function convertBooleansInData(array $data)
    {
        foreach($data as &$value) {
            if(!is_bool($value)) continue;
            $value = $value ? 'TRUE' : 'FALSE';
        }
        return $data;
    }
}