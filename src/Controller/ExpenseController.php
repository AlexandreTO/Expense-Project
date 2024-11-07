<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Expense;
use App\Event\ExpenseAddEvent;
use App\Form\ExpenseType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class ExpenseController extends AbstractController
{
    private EntityManagerInterface $em;
    private const ALLOWED_SORT_FIELDS = ['category', 'amount', 'date', 'description'];
    private const ALLOWED_SORT_DIRECTIONS = ['asc', 'desc'];
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, )
    {
        $this->em = $em;
        $this->serializer = $serializer;
    }

    #[Route('/expense/new', name: 'expense_new')]
    public function newExpense(Request $request, EventDispatcherInterface $eventDispatcher): Response
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
            $expense->setUser(user: $user);
            $this->em->flush();

            $eventDispatcher->dispatch(new ExpenseAddEvent($expense), eventName: ExpenseAddEvent::NAME);

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

        $expenses = $this->em->getRepository(Expense::class)->findExpenseByUserIdSort($user->getId(), $sortField, $sortDirection);

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
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $expenses = $this->em->getRepository(Expense::class)->findExpenseByUserId($user->getId());

        $csv = $this->serializer->serialize($expenses, 'csv', [
            CsvEncoder::HEADERS_KEY => ['Category', 'Amount', 'Date', 'Description']
        ]);

        $response = new Response($csv);
        // Set the headers
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="expenses.csv"');

        return $response;
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
