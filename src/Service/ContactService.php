<?php

declare(strict_types=1);

namespace App\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class ContactService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function modify($message, $request): void
    {
        switch ($request->query->get('action')) {
            case 'delete':
                $this->entityManager->remove($message);
                break;
            case 'done':
                {
                    $message->setStatus('done');
                    $message->setUpdatedAt(new DateTime('now'));
                }
                break;
            case 'undone':
                {
                    $message->setStatus('pending');
                    $message->setUpdatedAt(new DateTime('now'));
                }
                break;
        }

        $this->entityManager->flush();
    }
}