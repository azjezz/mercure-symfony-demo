<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Message
{
    use Behavior\Identifiable;
    use Behavior\Trackable;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $author = null;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $content = '';

    /**
     * @ORM\ManyToOne(targetEntity=ChatRoom::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?ChatRoom $room = null;
    
    public static function create(User $author, string $content): Message
    {
        $message = new self();
        $message->author = $author;
        $message->content = $content;
        $message->updateTimestamps();
        
        return $message;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getRoom(): ?ChatRoom
    {
        return $this->room;
    }

    public function setRoom(?ChatRoom $room): self
    {
        $this->room = $room;

        return $this;
    }
}
