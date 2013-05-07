<?php
namespace PlusPull;

use PlusPull\Commands\Show;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new Show();

        return $defaultCommands;
    }
}
