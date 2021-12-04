<?php

namespace App\MessageHandler\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

use App\Message\Command\WhatsApp;

class WhatsAppHandler implements MessageHandlerInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(WhatsApp $whatsApp)
    {
        $this->logger->info('Message to: '.$whatsApp->getPhone());
        $this->logger->info('----------------------------------');
        $this->logger->info($whatsApp->getMessage());
        $this->logger->info('----------------------------------');
    }
}
