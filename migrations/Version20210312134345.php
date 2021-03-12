<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210312134345 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create mercure chat room.';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO public.chat_room (name) VALUES ('Tech');");
        $this->addSql("INSERT INTO public.chat_room (name) VALUES ('Mercure');");
        $this->addSql("INSERT INTO public.chat_room (name) VALUES ('Symfony');");
        $this->addSql("INSERT INTO public.chat_room (name) VALUES ('Api Platform');");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DELETE FROM public.chat_room");
    }
}
