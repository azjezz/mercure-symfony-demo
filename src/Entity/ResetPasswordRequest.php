<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Psl;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ResetPasswordRequestRepository")
 */
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use Behavior\Identifiable;
    use ResetPasswordRequestTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private ?User $user = null;

    public function __construct(User $user, DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->user = $user;
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    public function getUser(): User
    {
        $user = $this->user;
        Psl\invariant(null !== $user, 'reset password request has not been initialized yet.');

        /** @var User $user */
        return $user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
