<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ChatRoom;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<ChatRoom>
 */
final class ChatRoomRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatRoom::class);
    }
}
