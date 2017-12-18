<?php

namespace Syntax\Admin;

use Clue\React\Stdio\Stdio;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Syntax\ChristmasContainer;
use Syntax\Exception\NotFoundCommandException;

class Input
{
    /**
     * @var Stdio
     */
    private $stdio;

    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * Input constructor.
     * @param LoopInterface $loop
     * @param ConsoleOutput $output
     */
    public function __construct(LoopInterface $loop, ConsoleOutput $output)
    {
        $this->stdio = new Stdio($loop);
        $this->output = $output;
    }

    /**
     * @param string $line
     */
    public function readLine($line)
    {
        try {
            $command = $this->findCommand($line);
            $command->exec($this->output);
        } catch(NotFoundCommandException $e) {
            $this->output->writeln(sprintf('<error>%s</error>', $e->getMessage()).PHP_EOL);
        }
    }

    /**
     * Init admin input
     */
    public function init()
    {
        $this->stdio->on('line', [$this, 'readLine']);
    }

    /**
     * @param $name
     * @return null|CommandInterface
     * @throws NotFoundCommandException
     */
    public function findCommand($name)
    {
        if(ChristmasContainer::getInstance()->has($name.'Command')) {
            return ChristmasContainer::getInstance()->get($name.'Command');
        }

        throw new NotFoundCommandException(sprintf('Not round "%s" command!', $name));
    }
}