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
    public function newExpense(Request $request, EntityManagerInterface $em): Response
    {
        $expense = new Expense();

        $form = $this->createForm(ExpenseType::class, $expense);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($expense);
            $em->flush();

            return $this->redirectToRoute('expense_list');
        }

        return $this->render('expense/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/expense/list', name: 'expense_list')]
    public function listExpenses(Request $request, EntityManagerInterface $em): Response
    {
        $sortField = $request->query->get('sort', 'category');
        $sortDirection = $request->query->get('direction', 'asc');

        $expenses = $em->getRepository(Expense::class)->findBy([], [$sortField => $sortDirection]);

        if ($request->isXmlHttpRequest()) {
            // Return only the table rows for AJAX requests
            return $this->render('expense/_table_rows.html.twig', [
                'expenses' => $expenses,
            ]);
        }

        return $this->render('expense/list.html.twig', [
            'expenses' => $expenses,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
        ]);
    }

}
