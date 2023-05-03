<?php

namespace App\Controller;

use App\Entity\Abonnement;
use App\Form\AbonnementType;
use App\Repository\AbonnementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\StripeClient;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


#[Route('/abonnementf')]
class AbonnementfController extends AbstractController
{

    private $stripe;

    public function __construct(StripeClient $stripe)
    {
        $this->stripe = $stripe;
    }

    public function calculateSubscriptionPrice($subscriptionPeriod)
    {
        $words = explode(' ', $subscriptionPeriod); // Sépare la période en mots
        $coeff = (int)$words[0]; // Coefficient numérique
        
        $keyword = strtolower($words[1]); // Mot clé (jours, mois, semaines, etc.)
        
        $price = 0; // Variable de prix
        
        switch ($keyword) {
            case 'jour':
            case 'jours':
                $price = $coeff * 10; // Prix par jour, à titre d'exemple
                break;
            case 'semaine':
            case 'semaines':
                $price = $coeff * 50; // Prix par semaine, à titre d'exemple
                break;
            case 'mois':
                $price = $coeff * 200; // Prix par mois, à titre d'exemple
                break;
            case 'an':
            case 'annuel':
                $price = $coeff * 1000; // Prix annuel, à titre d'exemple
                break;
            default:
                // Gérer les cas où le mot clé n'est pas reconnu
                break;
        }
        
        return $price;
    }

    #[Route('/', name: 'app_abonnementf_index', methods: ['GET'])]
    public function index(AbonnementRepository $abonnementRepository): Response
    {
        return $this->render('abonnementf/indexfront.html.twig', [
            'abonnements' => $abonnementRepository->findAll(),
        ]);
    }

    #[Route('/subscribe/{id}', name: 'app_subscribe')]
    public function subscribe(MailerInterface $mailer,Abonnement $abonnement, SessionInterface $session): Response
    {
        $email = 'nahdii13@gmail.com'; // retrive email (conducteur) from session
        // Create a new payment intent
        $intent = $this->stripe->paymentIntents->create([
            'amount' => $abonnement->getPrixAb(),
            'currency' => 'usd',
        ]);

        // Generate a payment link
        $link = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'abonnement'.$abonnement->getTypeAb(),
                        ],
                        'unit_amount' => $abonnement->getPrixAb(),
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => 'http://example.com/success',
            'cancel_url' => 'http://example.com/cancel'
            //'payment_intent_data' => $intent->id,
        ])->url;


        $mail = (new Email())
            ->from('hello@example.com')
            ->to($email)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('proceed to payement!')
            ->text('comlete subscription!')
            ->html('<a href="'.$link.'">lien pour valider paiement !</a>');

        $mailer->send($mail);
        // Store the success payment message in the session as a flash message
    $session->getFlashBag()->add('success', 'link sent to your email to complete your payement!');

    // Redirect to the specified route with the appropriate status code
    return new RedirectResponse($this->generateUrl('app_contratf_index'), Response::HTTP_SEE_OTHER);
    }



    #[Route('/back', name: 'app_abonnementb_index', methods: ['GET'])]
    public function indexback(Request $request, AbonnementRepository $abonnementRepository): Response
    {
        $sortBy = $request->query->get('sort_by', 'id'); // Default sorting by 'id'
        $searchKeyword = $request->query->get('search_keyword', '');

        $abonnements = $abonnementRepository->findByKeywordAndSort($searchKeyword, $sortBy);

        return $this->render('abonnementf/indexback.html.twig', [
            'abonnements' => $abonnements,
            'sortBy' => $sortBy,
            'searchKeyword' => $searchKeyword,
        ]);
    }
    #[Route('/new', name: 'app_abonnementf_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AbonnementRepository $abonnementRepository): Response
    {
        $abonnement = new Abonnement();
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {
            $abonnement->setPrixAb($this->calculateSubscriptionPrice($abonnement->getTypeAb()));
            $abonnementRepository->save($abonnement, true);

            return $this->redirectToRoute('app_abonnementf_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('abonnementf/new.html.twig', [
            'abonnement' => $abonnement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_abonnementf_show', methods: ['GET'])]
    public function show(Abonnement $abonnement): Response
    {
        return $this->render('abonnementf/show.html.twig', [
            'abonnement' => $abonnement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_abonnementf_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Abonnement $abonnement, AbonnementRepository $abonnementRepository): Response
    {
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $abonnement->setPrixAb($this->calculateSubscriptionPrice($abonnement->getTypeAb()));
            $abonnementRepository->save($abonnement, true);

            return $this->redirectToRoute('app_abonnementf_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('abonnementf/edit.html.twig', [
            'abonnement' => $abonnement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_abonnementf_delete', methods: ['POST'])]
    public function delete(Request $request, Abonnement $abonnement, AbonnementRepository $abonnementRepository): Response
{
       if ($this->isCsrfTokenValid('delete'.$abonnement->getId(), $request->request->get('_token'))) {
            $abonnementRepository->remove($abonnement, true);
        }

        return $this->redirectToRoute('app_abonnementf_index', [], Response::HTTP_SEE_OTHER);
    }


    
}
