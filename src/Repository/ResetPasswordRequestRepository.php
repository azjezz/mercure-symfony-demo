<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use DateTimeInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psl;
use SymfonyCasts\Bundle\ResetPassword\Persistence\Repository\ResetPasswordRequestRepositoryTrait;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

/**
 * @extends AbstractRepository<ResetPasswordRequest>
 */
final class ResetPasswordRequestRepository extends AbstractRepository implements ResetPasswordRequestRepositoryInterface
{
    use ResetPasswordRequestRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    public function createResetPasswordRequest(
        object $user,
        DateTimeInterface $expiresAt,
        string $selector,
        string $hashedToken
    ): ResetPasswordRequest {
        Psl\invariant($user instanceof User, 'Invalid user entity.');

        return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
    }
}
