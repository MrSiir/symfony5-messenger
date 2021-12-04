<?php

namespace App\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

use App\Message\Command\WhatsApp;

class ExternalJsonMessageSerializer implements SerializerInterface
{
    public function decode(array $encodedEnvelope): Envelope
    {
        $body = $encodedEnvelope['body'];
        $headers = $encodedEnvelope['headers'];

        $data = json_decode($body, true);

        if (null === $data) {
            throw new MessageDecodingFailedException('Invalid JSON');
        }

        if (!isset($headers['type'])) {
            throw new MessageDecodingFailedException('Missing "type" header');
        }

        switch ($headers['type']) {
            case 'App\Message\Command\WhatsApp':
                $envelope = $this->createWhatsAppEnvelope($data);
                break;
            default:
                throw new MessageDecodingFailedException(sprintf('Invalid type "%s"', $headers['type']));
        }


        // in case of redelivery, unserialize any stamps
        $stamps = [];
        if (isset($headers['stamps'])) {
            $stamps = unserialize($headers['stamps']);
        }
        $envelope = $envelope->with(... $stamps);

        return $envelope;
    }

    public function encode(Envelope $envelope): array
    {
        // this is called if a message is redelivered for "retry"
        $message = $envelope->getMessage();

        // expand this logic later if you handle more than
        // just one message class
        if ($message instanceof WhatsApp) {
            // recreate what the data originally looked like
            $data = [
                'phone' => $message->getPhone(),
                'message' => $message->getMessage()
            ];
            $type = 'whatsapp';
        } else {
            throw new \Exception('Unsupported message class');
        }

        $allStamps = [];
        foreach ($envelope->all() as $stamps) {
            $allStamps = array_merge($allStamps, $stamps);
        }

        return [
            'body' => json_encode($data),
            'headers' => [
                // store stamps as a header - to be read in decode()
                'stamps' => serialize($allStamps),
                'type' => $type,
            ],
        ];
    }

    private function createWhatsAppEnvelope(array $data): Envelope
    {
        if (!isset($data['phone'])) {
            throw new MessageDecodingFailedException('Missing the phone key');
        }
        if (!isset($data['message'])) {
            throw new MessageDecodingFailedException('Missing the message key');
        }

        $message = new WhatsApp($data['phone'], $data['message']);

        $envelope = new Envelope($message);

        return $envelope;
    }
}
