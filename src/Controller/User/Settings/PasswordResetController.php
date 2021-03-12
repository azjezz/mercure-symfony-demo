<?php

declare(strict_types=1);

namespace App\Controller\User\Settings;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Security;
use App\Service\Responder;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psl\Type;

#[Route('/user/settings/password-reset')]
final class PasswordResetController
{
    public function __construct(
        private Security $security,
        private Responder $responder,
        private UserRepository $users,
    ) {
    }

    /**
     * @throws ORMException
     */
    #[Route('/enable', name: 'user_settings_password_reset_enable', methods: ['POST'])]
    public function enable(Request $request): RedirectResponse
    {
        return $this->toggle($request, csrf_token_id: 'user.password-reset.enable', enabled: true);
    }

    /**
     * @throws ORMException
     */
    #[Route('/disable', name: 'user_settings_password_reset_disable', methods: ['POST'])]
    public function disable(Request $request): RedirectResponse
    {
        return $this->toggle($request, csrf_token_id: 'user.password-reset.disable', enabled: false);
    }

    /**
     * @throws ORMException
     */
    private function toggle(Request $request, string $csrf_token_id, bool $enabled): RedirectResponse
    {
        $this->security->denyAccessUnlessGranted(User::ROLE_USER);

        $csrf_token = Type\string()->assert($request->request->get('token', ''));

        $this->security->denyAccessUnlessCsrfTokenIsValid($csrf_token_id, $csrf_token);

        $user = $this->security->getAuthenticatedUser();
        $user->setPasswordResetEnabled($enabled);
        $this->users->save($user);

        return $this->responder->route('user_settings');
    }
}
