<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Products;
use App\Form\EditProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash("message", "Profil mis à jour");
            return $this->redirectToRoute('users');
        }
        return $this->render('users/editprofile.html.twig', [
            'editProfile' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/users/password/edit", name="users_password_edit")
     */
    public function editPassword(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        if($request->isMethod('POST')) {
            $user = $this->getUser();
            
            // On vérifie si les 2 mots de passe sont identiques
            if($request->request->get('password') == $request->request->get('confirmPassword')){
                $user->setPassword($encoder->encodePassword($user, $request->request->get('password')));
                $manager->flush();
                $this->addFlash("message", "Le mot de passe a bien été mis à jour");
                return $this->redirectToRoute('users');
            }else{
                $this->addFlash("error", "Les deux mots de passe ne sont pas identiques");
            }
        }
        return $this->render('users/editpassword.html.twig');
    }

    /**
     * Permet d'afficher le récap de paiement d'un produit et de payer, puis envoi d'un email
     * @Route("/users/payment", name="users_payment")
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function payment(Request $request, MailerInterface $mailer)
    {
        if ($request->isMethod('POST')){
            \Stripe\Stripe::setApikey('sk_test_LERnpSLVxNTs5MjAuAYiaHUb004CDn5leq');
            $user = $this->getuser();
            $cust = \Stripe\Customer::create([
                'description' => $user->getFullname(),
                'email' => $user->getEmail(),
                'address' => [
                    'line1' => $user->getAddress(),
                    'city' => $user->getCity(),
                    'postal_code' => $user->getPostalCode()
                ],
                'phone' => $user->getPhone()
            ]);

            $pi = \Stripe\PaymentIntent::create([
                'amount' => $_POST['total'] * 100,
                'currency' => 'eur',
                'payment_method_types' => ['card'],
                'description' => 'first test charge !',
                'customer' => $cust->id
            ]);
            $payment_intent = \Stripe\PaymentIntent::retrieve($pi->id);
            $payment_intent->confirm([
                'payment_method' => 'pm_card_visa',
                'receipt_email' => $user->getEmail()
                ]);
                
                if ($this->addFlash('success', "Paiement réussi")) {
                    $email = (new TemplatedEmail())
                    ->from('fabcelou@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Récapitulatif achat') 
                    ->htmlTemplate('emails/historyPaid.html.twig');
                    $mailer->send($email);
                };
            return $this->redirectToRoute('users_history');
        }
    }

    /**
     * Permet d'afficher l'historique des paiements
     * @Route("users/history", name="users_history")
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function historyPayment()
    {
        return $this->render('users/payment.html.twig', [
        ]);
    }
}