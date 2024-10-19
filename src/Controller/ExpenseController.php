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
    public function listExpenses(EntityManagerInterface $em): Response
    {
        $expenses = $em->getRepository(Expense::class)->findAll();

        return $this->render('expense/list.html.twig', [
            'expenses' => $expenses,
        ]);
    }
}
