<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\Expense;
use App\Form\ExpenseType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Metadata\Covers;
use Symfony\Component\Form\Test\TypeTestCase;

// Add coverage attributes for code coverage
#[CoversClass(ExpenseType::class)]
#[CoversClass(Expense::class)]
class ExpenseTypeTest extends TypeTestCase
{
    #[Covers('App\Form\ExpenseType::buildForm')]
    #[Covers('App\Form\ExpenseType::configureOptions')]
    public function testValidFormData(): void
    {
        // Mock object to test with
        $expense = new Expense();
        $expense->setCategory('food');
        $expense->setAmount(100.50);
        $expense->setDate(new \DateTime('2024-10-18'));
        $expense->setDescription('Lunch meeting expense');

        // Simulating a form data submission
        $formData = [
            'category' => 'food',
            'amount' => 100.50,
            'date' => '2024-10-18',
            'description' => 'Lunch meeting expense',
        ];

        // Form creation for the data submission
        $form = $this->factory->create(ExpenseType::class);
        $form->submit($formData);

        // Making sure the form is correctly submitted and valid
        $this->assertTrue($form->isSynchronized());

        // Check the form data if it matches the entity object
        $submittedExpense = $form->getData();

        // assertSame for stricter comparisons
        $this->assertSame($expense->getCategory(), $submittedExpense->getCategory());
        $this->assertSame($expense->getAmount(), $submittedExpense->getAmount());

        // Since dates are objects, you cannot use assertSame; instead, we compare the string representation
        $this->assertSame($expense->getDate()->format('Y-m-d'), $submittedExpense->getDate()->format('Y-m-d'));

        $this->assertSame($expense->getDescription(), $submittedExpense->getDescription());
    }

    #[Covers('App\Form\ExpenseType::buildForm')]
    #[Covers('App\Form\ExpenseType::configureOptions')]
    public function testInvalidData(): void
    {
        $formData = [
            'category' => '',
            'amount' => -50,
            'date' => 'invalid-date',
            'description' => '',
        ];

        $form = $this->factory->create(ExpenseType::class);
        $form->submit($formData);

        $this->assertFalse($form->isValid());
    }
}