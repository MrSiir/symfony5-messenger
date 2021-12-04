<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Messenger\MessageBusInterface;

use App\Message\Command\WhatsApp;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->render('send.html.twig');
    }

    /**
     * @Route("/send", name="send")
     */
    public function send(Request $request): Response
    {
        $phone = $request->request->get('phone');
        $message = $request->request->get('message');

        if (!empty($phone) && !empty($message)) {
            $whatsApp = new WhatsApp($phone, $message);
            $this->dispatchMessage($whatsApp);
            return $this->json(['phone' => $whatsApp->getPhone(), 'message' => $whatsApp->getMessage()]);
        }

        return $this->json(['error' => true]);
    }
}