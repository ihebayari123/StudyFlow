<?php

namespace App\Controller;
use Symfony\Component\Mime\Email;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function cart(): Response
    {
        return $this->render('cart/cart.html.twig');
    }

    #[Route('/cart/checkout', name: 'app_cart_checkout')]
    public function checkout(): Response
    {
        return $this->render('cart/checkout.html.twig');
    }

    #[Route('/cart/create-checkout-session', name: 'app_create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $items = $data['items'] ?? [];
        $customerName = $data['customer'] ['name'] ?? null;
        $customerEmail = $data['customer'] ['email'] ?? null;
        $customerAddress = $data['customer'] ['address'] ?? null;

        $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? $_SERVER['STRIPE_SECRET_KEY'] ?? null;
        if (!$stripeSecret) {
            return new JsonResponse(['error' => 'Stripe secret key not configured'], 500);
        }

        try {
            $stripe = new \Stripe\StripeClient($stripeSecret);

            $line_items = [];
            foreach ($items as $it) {
                $price = isset($it['price']) ? (int) round(floatval($it['price']) * 100) : 0;
                $quantity = isset($it['quantity']) ? (int) $it['quantity'] : 1;

                $line_items[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $it['name'] ?? 'Produit',
                        ],
                        'unit_amount' => $price,
                    ],
                    'quantity' => $quantity,
                ];
            }

            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => $line_items,
                'mode' => 'payment',
                'billing_address_collection' => 'required',
                'customer_email' => $customerEmail,
                'metadata' => [
                    'customer_name' => $customerName,
                    'customer_address' => $customerAddress,
                ],
                'success_url' => $this->generateUrl('app_cart_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->generateUrl('app_cart', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            return new JsonResponse(['url' => $session->url]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
///////////////////////////////////////////////////////////////////////////////////////////////////
 #[Route('/cart/success', name: 'app_cart_success')]
public function success(Request $request, MailerInterface $mailer): Response
{
    $sessionId = $request->query->get('session_id');
    $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? null;
    $stripe = new \Stripe\StripeClient($stripeSecret);
    $session = $stripe->checkout->sessions->retrieve($sessionId);

    $customerEmail = $session->customer_email;
    $customerName = $session->metadata->customer_name ?? 'Client';
    $amountTotal = $session->amount_total / 100;
    $currency = strtoupper($session->currency);

    try {
        $email = (new Email())
            ->from('hammamiomar706@gmail.com')
            ->to($customerEmail)
            ->subject('Confirmation de votre commande')
            ->text(sprintf(
                "Bonjour %s,\n\nMerci pour votre commande !\n\nTotal payé : %.2f %s\n\nNous vous remercions de votre confiance.",
                $customerName,
                $amountTotal,
                $currency
            ));

        $mailer->send($email);

    } catch (\Throwable $e) {
        // ne bloque pas la page
    }

    return $this->render('cart/success.html.twig', [
        'customerName' => $customerName,
        'amountTotal' => $amountTotal,
        'currency' => $currency,
    ]);
}
}
