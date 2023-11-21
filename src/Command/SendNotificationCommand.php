<?php
/** @noinspection PhpMultipleClassDeclarationsInspection */

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendNotificationCommand extends Command
{
    protected static $defaultName = 'send:notifications';

    private EntityManagerInterface $database;

    public function __construct(EntityManagerInterface $em)
    {
        $this->database = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send all on-time notifications to subscribers')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = $this->commandConsoleLogic();

        $io->success('Sent ' . $count . ' notifications.');

        return Command::SUCCESS;
    }

    private function commandConsoleLogic(): int
    {
        $count = 0;

        // some code, should return count of notifications, and set it to $count

        return $count;
    }
}
