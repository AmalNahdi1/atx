<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Form\ContratType;
use App\Repository\ContratRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Options;

use Dompdf\Dompdf;

#[Route('/contratf')]
class ContratfController extends AbstractController
{
    #[Route('/', name: 'app_contratf_index', methods: ['GET'])]
    public function index(ContratRepository $contratRepository): Response
    {
        return $this->render('contratf/indexfront.html.twig', [
            'contrats' => $contratRepository->findAll(),
        ]);
    }

    #[Route('/back', name: 'app_contratb_index', methods: ['GET'])]
    public function indexB(ContratRepository $contratRepository): Response
    {
        return $this->render('contratf/indexback.html.twig', [
            'contrats' => $contratRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contratf_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ContratRepository $contratRepository): Response
    {
        $contrat = new Contrat();
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contratRepository->save($contrat, true);

            return $this->redirectToRoute('app_contratf_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contratf/new.html.twig', [
            'contrat' => $contrat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contratf_show', methods: ['GET'])]
    public function show(Contrat $contrat): Response
    {
        return $this->render('contratf/show.html.twig', [
            'contrat' => $contrat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contratf_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contrat $contrat, ContratRepository $contratRepository): Response
    {
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contratRepository->save($contrat, true);

            return $this->redirectToRoute('app_contratf_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contratf/edit.html.twig', [
            'contrat' => $contrat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contratf_delete', methods: ['POST'])]
    public function delete(Request $request, Contrat $contrat, ContratRepository $contratRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contrat->getId(), $request->request->get('_token'))) {
            $contratRepository->remove($contrat, true);
        }

        return $this->redirectToRoute('app_contratf_index', [], Response::HTTP_SEE_OTHER);
    }
    // pdf method : 


    #[Route('/generate_pdf/{id}', name: 'generate_pdf', methods: ['GET', 'POST'])]
    public function generateContrat(Contrat $contrat)
    {

        $html = $this->renderView('contratf/pdf.html.twig', [
            'contrat' => $contrat
        ]);


        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="contrat.pdf"');

        return $response;
    }

    //download pdf : 


    #[Route('/download_pdf/{id}', name: 'download_pdf', methods: ['GET', 'POST'])]

    public function downloadPdf(Contrat $contrat)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        // Render the HTML template for the PDF
        $html = $this->renderView('contratf/pdf.html.twig', [
            'contrat' => $contrat,
        ]);

        // Load the HTML into Dompdf
        $dompdf->loadHtml($html);

        // Set the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Create a new Symfony response with the PDF content
        $response = new Response($pdfContent);

        // Set the headers for a PDF file download
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="contrat_' . $contrat->getId() . '.pdf"');

        return $response;
    }

}
