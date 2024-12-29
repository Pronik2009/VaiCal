<?php
/** @noinspection PhpMultipleClassDeclarationsInspection */

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle; 

class SendNotificationCommand extends Command
{
    protected static $defaultName = 'send:notifications';
    private NotificatorProcessor $notificator;

    public function __construct(EntityManagerInterface $em)
    {
        $this->notificator = new NotificatorProcessor($em);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send all on-time notifications to subscribers');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws FirebaseException
     * @throws MessagingException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->notificator->initNotification();

        $io->success('Sent ' . $result['success'] . ' notifications.');
        $io->warning('Deleted ' . $result['fail'] . ' devices.');

        return Command::SUCCESS;
    }
}
