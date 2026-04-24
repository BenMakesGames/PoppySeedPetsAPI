<?php
declare(strict_types = 1);

namespace App\Model;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Functions\PetActivityLogFactory;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Example usage:
 *
 * <code>
 * $groupLogHelper = new MultiPetActivityLogHelper($em);
 * $message = 'This message is the same for all the pets.';
 *
 * foreach($pets as $pet)
 * {
 *     $groupLogHelper->createGroupLog($pet, $message);
 * }
 * </code>
 */
class MultiPetActivityLogHelper
{
    private array $usersAlerted = [];

    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {}

    /**
     * Creates a read or unread log, depending on if the pet passed in has an owner who is included in usersAlerted
     * For groups so users don't get 'copies' of messages
     */
    public function createGroupLog(Pet $pet, string $message) : PetActivityLog
    {
        $alreadyMessagedThisPlayer = in_array($pet->getOwner()->getId(), $this->usersAlerted);

        if(!$alreadyMessagedThisPlayer)
            $this->usersAlerted[] = $pet->getOwner()->getId();

        $log = $alreadyMessagedThisPlayer
            ? PetActivityLogFactory::createReadLog($this->em, $pet, $message)
            : PetActivityLogFactory::createUnreadLog($this->em, $pet, $message);

        return $log;
    }
}