<?php

declare(strict_types=1);

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
    private EntityManagerInterface $em;
    private const ALLOWED_SORT_FIELDS = ['category', 'amount', 'date', 'description'];
    private const ALLOWED_SORT_DIRECTIONS = ['asc', 'desc'];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/expense/new', name: 'expense_new')]
    public function newExpense(Request $request): Response
    {
        $expense = new Expense();

        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to create an expense.');
        }

        $form = $this->createForm(ExpenseType::class, $expense);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($expense);
            $expense->setUser($user);
            $this->em->flush();

            return $this->redirectToRoute('expense_list');
        }

        return $this->render('expense/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/expense/list', name: 'expense_list')]
    public function listExpenses(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $sortField = $request->query->get('sort', 'category');
        $sortDirection = $request->query->get('direction', 'asc');

        $sortField = $this->getValidSortField($request->query->get('sort', 'category'));
        $sortDirection = $this->getValidSortDirection($request->query->get('direction', 'asc'));

        $expenses = $this->em->getRepository(Expense::class)->findUserById($user->getId(), $sortField, $sortDirection);

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

    #[Route('/expenses/export/csv', name: 'export_expenses_csv')]
    public function exportToCsv(): Response
    {
        $expenses = $this->em->getRepository(Expense::class)->findAll();
        $csv = $this->generateCSV($expenses);

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="expenses.csv"');

        return $response;
    }

    private function generateCSV(array $expenses): string
    {
        $handle = fopen('php://memory', 'wb');

        // Headers
        fputcsv($handle, ['Category', 'Amount', 'Date', 'Description']);
        foreach ($expenses as $expense) {
            fputcsv($handle, [
                $expense->getCategory(),
                $expense->getAmount(),
                $expense->getDate()->format('Y-m-d'),
                $expense->getDescription(),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    private function getValidSortField(string $sortField): string
    {
        return in_array($sortField, self::ALLOWED_SORT_FIELDS) ? $sortField : 'category';
    }

    private function getValidSortDirection(string $sortDirection): string
    {
        return in_array(strtolower($sortDirection), self::ALLOWED_SORT_DIRECTIONS) ? $sortDirection : 'asc';
    }
}
