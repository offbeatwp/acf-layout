<?php
namespace OffbeatWP\AcfLayout\Console;

use OffbeatWP\Console\AbstractCommand;

class Install extends AbstractCommand
{
    public function execute($args, $argsNamed)
        $this->copyFolder(dirname(__FILE__) . '/../../templates/Row', get_template_directory() . '/components/Row');
        $this->copyFolder(dirname(__FILE__) . '/../../templates/Component', get_template_directory() . '/components/Component');
    }

    protected function copyFolder($source, $dest)
    {
        mkdir($dest, 0755);
        foreach ($iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST) as $item) {

            if ($item->isDir()) {
                mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }

}
