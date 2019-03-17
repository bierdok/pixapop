<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CreateSymlinkCommand extends Command
{
    protected static $defaultName = 'app:create-symlink';

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @param string $projectDir
     */
    public function setProjectDir(string $projectDir): void
    {
        $this->projectDir = $projectDir;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileSystem = new Filesystem();
        $fileSystem->symlink(getenv('APP_PHOTO_DIR'), $this->projectDir . '/public/photos');
    }
}
