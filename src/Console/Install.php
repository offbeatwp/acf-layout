<?php
namespace OffbeatWP\AcfLayout\Console;

use OffbeatWP\Console\AbstractCommand;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Install extends AbstractCommand
{
    public const COMMAND = 'acf-layout:install';

    public function execute($args, $argsNamed)
    {
        $this->copyFolder(__DIR__ . '/../../templates/Row', get_template_directory() . '/components/Row');
        $this->copyFolder(__DIR__ . '/../../templates/Component', get_template_directory() . '/components/Component');

        copy(__DIR__ . '/../../templates/design.php', get_template_directory() . '/config/design.php');

        $this->success('Successfully installed');
    }

    protected function copyFolder($source, $dest)
    {
        if (is_dir($dest)) {
            $this->error("Component already exists ({$dest})");
            exit;
        }

        mkdir($dest, 0755);
        foreach ($iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST) as $item) {

            if ($item->isDir()) {
                mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }

}
