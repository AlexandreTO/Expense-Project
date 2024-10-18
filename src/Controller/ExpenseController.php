<?php
namespace App\Controller;

use App\Entity\Expense;
use App\Form\ExpenseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExpenseController extends AbstractController
{
    #[Route('/expense/new', name: 'expense_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Create a new Expense entity
        $expense = new Expense();

        // Create the form and bind it to the entity
        $form = $this->createForm(ExpenseType::class, $expense);

        // Handle the request
        $form->handleRequest($request);

        // If the form is submitted and valid, persist the expense to the database
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($expense);
            $em->flush();

            // Redirect or show a success message
            return $this->redirectToRoute('expense_list');
        }

        // Render the form in a template
        return $this->render('expense/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/expense/list', name: 'expense_list')]
    public function list(EntityManagerInterface $em): Response
    {
        // Fetch all expenses
        $expenses = $em->getRepository(Expense::class)->findAll();

        return $this->render('expense/list.html.twig', [
            'expenses' => $expenses,
        ]);
    }
}
