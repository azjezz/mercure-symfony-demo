<?php

declare(strict_types=1);

namespace App\Service;

use Psl\Type;
use App\Entity\User;
use App\Form\User\RegisterType;
use App\Repository\UserRepository;
use App\Security\PasswordManager;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class Registration
{
    public function __construct(
        private FormFactoryInterface $factory,
        private UserRepository $repository,
        private PasswordManager $passwordManager
    ) {
    }

    public function createForm(User $user): FormInterface
    {
        return $this->factory->create(RegisterType::class, $user);
    }

    /**
     * @throws ORMException
     */
    public function register(User $user): void
    {
        $password = Type\string()->assert($user->getNewPassword());

        $hash = $this->passwordManager->encodePassword($user, $password);
        $this->passwordManager->upgradePassword($user, $hash, persist: false);

        $this->repository->save($user);
    }
}
