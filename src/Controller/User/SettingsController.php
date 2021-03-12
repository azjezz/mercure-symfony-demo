<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\User;
use App\Form\User\Settings;
use App\Repository\UserRepository;
use App\Security\PasswordManager;
use App\Security\Security;
use App\Service\Responder;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\Error as TwigError;
use Psl\Type;

#[Route('/user')]
final class SettingsController
{
    public function __construct(
        private Responder $responder,
        private UserRepository $repository,
        private FormFactoryInterface $factory,
        private Security $security,
        private PasswordManager $password,
    ) {
    }

    /**
     * @throws ORMException
     * @throws TwigError
     */
    #[Route('/settings', name: 'user_settings', methods: ['GET', 'POST'])]
    public function settings(Request $request): Response
    {
        $this->security->denyAccessUnlessGranted(User::ROLE_USER);

        $user = $this->security->getAuthenticatedUser();

        $profileForm = $this->factory->create(Settings\ProfileType::class, $user);
        $passwordForm = $this->factory->create(Settings\ChangePasswordType::class, $user);
        $deleteForm = $this->factory->create(Settings\DeleteType::class, $user);

        $profileForm->handleRequest($request);
        $passwordForm->handleRequest($request);
        $deleteForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $this->repository->save($user);
        }

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $hash = $this->password->encodePassword($user, Type\string()->assert($user->getNewPassword()));

            $this->password->upgradePassword($user, $hash);
        }

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            /**
             * Delete the user from the database.
             */
            $this->repository->delete($user);

            /**
             * Force logout the user.
             */
            $this->security->invalidate($request->getSession());

            /**
             * Redirect the user back to the login page.
             */
            return $this->responder->route('user_login');
        }

        return $this->responder->render('user/settings/index.html.twig', [
            'user' => $this->security->getUser(),

            'profile_form' => $profileForm->createView(),
            'profile_updated' => $profileForm->isSubmitted() && $profileForm->isValid(),

            'password_form' => $passwordForm->createView(),
            'password_updated' => $passwordForm->isSubmitted() && $passwordForm->isValid(),

            'delete_form' => $deleteForm->createView(),
            'delete_attempted' => $deleteForm->isSubmitted(),
        ]);
    }
}
