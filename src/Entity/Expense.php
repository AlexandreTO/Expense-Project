<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Expense 
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $category;

    #[ORM\Column(type: 'decimal', scale: 2)]
    private $amount;

    #[ORM\Column(type: 'date')]
    private $date;

    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

}