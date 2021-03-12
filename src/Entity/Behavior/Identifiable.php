<?php

declare(strict_types=1);

namespace App\Entity\Behavior;

use Doctrine\ORM\Mapping as ORM;

trait Identifiable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
