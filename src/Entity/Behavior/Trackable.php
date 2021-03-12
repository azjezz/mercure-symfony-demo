<?php

declare(strict_types=1);

namespace App\Entity\Behavior;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Psl;
use Psl\Str;

trait Trackable
{
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected ?DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected ?DateTimeInterface $updatedAt = null;

    /**
     * Updates createdAt and updatedAt timestamps.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $dateTime = static::getCurrentDateTime();

        if (null === $this->createdAt) {
            $this->createdAt = $dateTime;
        }

        $this->updatedAt = $dateTime;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function isRevised(): bool
    {
        $createdAt = $this->getCreatedAt();
        $updatedAt = $this->getUpdatedAt();

        if (null === $updatedAt || null === $createdAt) {
            return false;
        }

        return $createdAt->getTimestamp() !== $updatedAt->getTimestamp();
    }

    public static function getCurrentDateTime(): DateTimeImmutable
    {
        // Create a datetime with microseconds
        $dateTime = DateTimeImmutable::createFromFormat('U.u', Str\format('%.6F', microtime(true)));
        Psl\invariant(false !== $dateTime, 'unable to create a datetime object with microseconds.');

        $dateTime = $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

        Psl\invariant(false !== $dateTime, 'unable set default timezone.');

        return $dateTime;
    }
}
