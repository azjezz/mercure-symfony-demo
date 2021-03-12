<?php

declare(strict_types=1);

namespace App\Controller\User;

use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
final class LogoutController
{
    #[Route('/logout', name: 'user_logout', methods: ['GET', 'POST'])]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall.
    }
}
