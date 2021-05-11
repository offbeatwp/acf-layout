<?php
namespace OffbeatWP\AcfLayout\Console;

use OffbeatWP\Console\AbstractCommand;

class CacheFields extends AbstractCommand
{
    const COMMAND = 'acf-layout:cache-fields';

    public function execute($args, $argsNamed)
    {
        $this->error('This cli command is deprecated');
    }
}
