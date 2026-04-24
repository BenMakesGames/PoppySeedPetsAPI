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
        private readonly EntityManagerInterface $em,
        private readonly string $message,
    )
    {}

    /**
     * Creates a read or unread log, depending on if the pet passed in has an owner who is included in usersAlerted
     * For logs that are the same for multiple pets, so users don't get 'copies' of messages
     */
    public function createGroupLog(Pet $pet) : PetActivityLog
    {
        $alreadyMessagedThisPlayer = in_array($pet->getOwner()->getId(), $this->usersAlerted);

        if(!$alreadyMessagedThisPlayer)
            $this->usersAlerted[] = $pet->getOwner()->getId();

        return $alreadyMessagedThisPlayer
            ? PetActivityLogFactory::createReadLog($this->em, $pet, $this->message)
            : PetActivityLogFactory::createUnreadLog($this->em, $pet, $this->message);
    }
}