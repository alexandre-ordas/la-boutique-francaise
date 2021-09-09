<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    /**
     * OrderValidateController constructor.
     * @param $entityManager
     */

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {

        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if($order->getState() == 0) {
            //Vider la session cart
            $cart->remove();

            //Modifier le statut state  de notre commande en mettant 1

            $order->setState(1);
            $this->entityManager->flush();

            //Envoyer un email à notre client pour lui confirmer sa commande

            $mail = new Mail();
            $content = "Bonjour ".$order->getUser()->getFirstname()."<br>Merci pour votre commande";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre Commande La Boutique Française est validé', $content );

        }

        return $this->render('order_success/index.html.twig', [
            'order' => $order


            ]);
    }
}
