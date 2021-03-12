<?php

declare(strict_types=1);

namespace App\Entity;

use App\Security\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Psl\Arr;
use Psl\Str;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUser;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @UniqueEntity(
 *     fields={"username"},
 *     message="registration.username.used",
 *     groups={"registration"}
 * )
 * @UniqueEntity(
 *     fields={"email"},
 *     message="registration.email.used",
 *     groups={"registration"}
 * )
 */
class User implements UserInterface
{
    use Behavior\Identifiable;
    use Behavior\Trackable;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     *
     * @Assert\Length(
     *     min="2",
     *     max="32",
     *     minMessage="registration.username.short",
     *     maxMessage="registration.username.long",
     *     groups={"registration"}
     * )
     * @Assert\Regex(
     *     pattern="/^[a-z]+$/i",
     *     htmlPattern="^[a-zA-Z]+$",
     *     message="registration.username.pattern",
     *     groups={"registration"}
     * )
     */
    private ?string $username = null;

    /**
     * @ORM\Column(type="string", length=300, unique=true)
     *
     * @Assert\Email(
     *     message="registration.email.invalid",
     *     groups={"registration", "settings"}
     * )
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="array")
     *
     * @var list<string>
     */
    private array $roles = [self::ROLE_USER];

    /**
     * @ORM\Column(type="string")
     */
    private ?string $password = null;

    /**
     * @UserPassword(
     *     message="settings.password.invalid",
     *     groups={"settings.password", "settings.delete"}
     * )
     * @Assert\NotBlank(groups={"settings.password"}, message="settings.password.blank")
     * @Assert\Length(
     *     charset="UTF-8",
     *     min=8,
     *     max=4069,
     *     minMessage="settings.password.short",
     *     maxMessage="settings.password.long",
     *     charsetMessage="settings.password.charset",
     *     groups={"settings.password"}
     * )
     */
    protected ?string $oldPassword = null;

    /**
     * @Assert\NotBlank(
     *     message="settings.password.blank",
     *     groups={"settings.password", "password-reset", "registration"}
     * )
     * @Assert\Length(
     *     charset="UTF-8",
     *     min=8,
     *     max=4069,
     *     minMessage="password.short",
     *     maxMessage="password.long",
     *     charsetMessage="password.charset",
     *     groups={"settings.password", "password-reset", "registration"}
     * )
     */
    protected ?string $newPassword = null;

    /**
     * Is the password reset feature enabled for this user?
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $passwordResetEnabled = true;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="author", orphanRemoval=true)
     */
    private $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->username ?? $this->email ?? '(unknown)';
    }

    /**
     * @return array{
     *      id: int|null,
     *      username: null|string,
     *      email: null|string,
     *      password: null|string,
     *      roles: array<array-key, string>,
     *      password_reset_enabled: bool
     * }
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'roles' => $this->roles,
            'password_reset_enabled' => $this->passwordResetEnabled,
        ];
    }

    public function __unserialize(array $data): void
    {
        /** @var array{id: int|null, username: null|string, email: null|string, password: null|string, roles: array<array-key, string>, password_reset_enabled: bool} $data */
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->email = $data['email'];
        $this->roles = Arr\values($data['roles']);
        $this->password = $data['password'];
        $this->passwordResetEnabled = $data['password_reset_enabled'];
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->username ?? '';
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!Arr\contains($roles, self::ROLE_USER)) {
            $roles[] = self::ROLE_USER;
        }

        return $roles;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $roles = Arr\unique(Arr\map(
            Arr\concat([self::ROLE_USER], $roles),
            fn(string $role): string => Str\uppercase($role)
        ));

        $this->roles = Arr\values($roles);

        return $this;
    }

    /**
     * Return true if the user has one of the given roles.
     */
    public function hasRole(string ...$roles): bool
    {
        foreach ($roles as $role) {
            if (Arr\contains($this->getRoles(), $role)) {
                return true;
            }
        }

        return false;
    }

    public function addRole(string $role): self
    {
        if ($this->hasRole($role)) {
            return $this;
        }

        $roles = $this->getRoles();
        $roles[] = $role;

        $this->setRoles($roles);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(): void
    {
        $this->oldPassword = null;
        $this->newPassword = null;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): void
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    /**
     * This method is only used during the account delete, and password change process.
     *
     * Plain password is *never* stored.
     */
    public function setOldPassword(?string $oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    /**
     * This method is only used during the registration, and password change process.
     *
     * Plain password is *never* stored.
     */
    public function setNewPassword(?string $newPassword): void
    {
        $this->newPassword = $newPassword;
    }

    public function isPasswordResetEnabled(): bool
    {
        return $this->passwordResetEnabled;
    }

    public function setPasswordResetEnabled(bool $passwordResetEnabled): self
    {
        $this->passwordResetEnabled = $passwordResetEnabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(SymfonyUser $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        if ($user->getId() !== $this->getId()) {
            return false;
        }

        if ($user->getUsername() !== $this->getUsername()) {
            return false;
        }

        if ($user->getEmail() !== $this->getEmail()) {
            return false;
        }

        if ($user->getPassword() !== $this->getPassword()) {
            return false;
        }

        if ($user->getRoles() !== $this->getRoles()) {
            return false;
        }

        if ($user->isPasswordResetEnabled() !== $this->isPasswordResetEnabled()) {
            return false;
        }

        return true;
    }

    public function getPasswordResetEnabled(): ?bool
    {
        return $this->passwordResetEnabled;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setAuthor($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getAuthor() === $this) {
                $message->setAuthor(null);
            }
        }

        return $this;
    }
}
