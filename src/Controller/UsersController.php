<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Users;
use App\Entity\Products;
use App\Form\EditProfileType;
use Mollie\Api\MollieApiClient;
use App\Repository\CartRepository;
use Symfony\Component\Mime\Address;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersController extends AbstractController
{
    /**
     * @Route("/users", name="users")
     */
    public function index()
    {
        return $this->render('users/index.html.twig');
    }

    /**
     * @Route("/users/profile/edit", name="users_profile_edit")
     */
    public function editProfile(Request $request, EntityManagerInterface $manager)
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('message', 'Profil mis à jour');

            return $this->redirectToRoute('users');
        }

        return $this->render('users/editprofile.html.twig', [
            'editProfile' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users/password/edit", name="users_password_edit")
     */
    public function editPassword(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        if ($request->isMethod('POST')) {
            $user = $this->getUser();

            // On vérifie si les 2 mots de passe sont identiques
            if ($request->request->get('password') == $request->request->get('confirmPassword')) {
                $user->setPassword($encoder->encodePassword($user, $request->request->get('password')));
                $manager->flush();
                $this->addFlash('message', 'Le mot de passe a bien été mis à jour');

                return $this->redirectToRoute('users');
            }
            $this->addFlash('error', 'Les deux mots de passe ne sont pas identiques');
        }

        return $this->render('users/editpassword.html.twig');
    }

    /**
     * Permet d'afficher le récap de paiement d'un produit et de payer, puis envoi d'un email.
     *
     * @Route("/users/payment", name="users_payment")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function payment(Request $request, CartRepository $cartRepo, EntityManagerInterface $manager)
    {
        if ($request->isMethod('POST')) {
            $user = $this->getUser();

            $cart = $cartRepo->getLastCart($user);
            $orderId = $cart->getRef();
            $amount = $_POST['total'];
            
            $mollie = new MollieApiClient();
            $mollie->setApiKey('test_ur9FQ8BgDwfePeGpwjBsx7UGhNrmUj');
            
            if(is_null($user->getCustomerId())){
                $customer = $mollie->customers->create([
                    'name' => $user->getFullName(),
                    'email' => $user->getEmail()
                ]);
                $customerId = $customer->id;             
            }else{
                $customerId = $user->getCustomerId();
            }
            
            // On crée un client et le paiement
            $payment = $mollie->payments->create([
                'locale' => 'fr_FR',
                'method' => 'creditcard',
                'amount' => [
                    'currency' => 'EUR',
                    'value' => (sprintf('%.2f', $amount)),
                ],
                'customerId' => $customerId,
                'description' => 'My first API Payment',
                'billingAddress' => [
                    'streetAndNumber' => $user->getAddress(),
                    'postalCode' => $user->getPostalCode(),
                    'city' => $user->getCity(),
                    'country' => 'Fr',
                ],
                'redirectUrl' => $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL ),
                'metadata' => [
                    'order_id' => $orderId,
                ],
            ]);
            $cart->setPaymentId($payment->id);
            $user->setCustomerId($customerId);
            $manager->persist($cart);
            $manager->flush();
            
            header('Location:'.$payment->getCheckoutUrl(), true, 303);
        }
        return $this->redirectToRoute('cart_index');
    }

    /**
     * Permet de valider le paiement car avec mollie se passe en 2 temps.
     *
     * @Route("/users/payment/success", name="payment_success")
     */
    public function validPayment(MailerInterface $mailer, CartRepository $cartRepo, EntityManagerInterface $manager)
    {
        $user = $this->getUser();
        $mollie = new MollieApiClient();
        $mollie->setApiKey('test_ur9FQ8BgDwfePeGpwjBsx7UGhNrmUj');
        $cart = $cartRepo->getLastCart($user);

        // On récupère le paiement pour voir s'il est payé
        $payment = $mollie->payments->get($cart->getPaymentId());
        if ($payment->isPaid()) {
            $cart->setStatut(true);
            $cartlines = $cart->getCartlines();
            foreach ($cartlines as $cartline){
                $product = $cartline->getProduct();
                $product->setStock($product->getStock() - $cartline->getQuantity());
                $manager->persist($product);
                if($product->getStock() <= 5){
                    $email = (new TemplatedEmail())
                        ->from('fabcelou@gmail.com')
                        ->to(new Address('fabcelou@gmail.com'))
                        ->subject('Alerte stock')
                        ->htmlTemplate('emails/alerteStock.html.twig')
                        ->context(['product' => $product])
                        ;
                    try {
                        $mailer->send($email);
                    } catch (TransportExceptionInterface $e) {
                        "Une erreur est survenue";
                    }
                }
            }
            $manager->persist($cart);
            $manager->flush();
        }

        // Si le paiement a réussi un message flash apparaît et on envoie un mail
        $orderId = $cart->getRef(); // ???
        
        if (!empty($payment)) {
            $this->addFlash('success', 'Paiement réussi');
            $email = (new TemplatedEmail())
                ->from('fabcelou@gmail.com')
                ->to(new Address($user->getEmail()))
                ->subject('Récapitulatif achat')
                ->htmlTemplate('emails/historyPaid.html.twig')
                ->context(['cart' => $cart])
                ;

            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                "Une erreur est survenue";
            }
        }

        return $this->redirectToRoute('users_history');
    }

    /**
     * Permet d'afficher l'historique des paiements.
     *
     * @Route("users/history", name="users_history")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function historyPayment(CartRepository $cartRepo)
    {
        return $this->render('users/payment.html.twig', [
            'carts' => $cartRepo->findAll(),
        ]);
    }
}