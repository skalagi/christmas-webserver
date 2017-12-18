<?php

namespace Syntax\Admin;

use Symfony\Component\Console\Output\OutputInterface;

interface CommandInterface
{
    public function exec(OutputInterface $output);
}