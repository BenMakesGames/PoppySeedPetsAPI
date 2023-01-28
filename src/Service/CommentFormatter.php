<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\User;
use App\Repository\PetRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;

class CommentFormatter
{
    private $petRepository;
    private $userRepository;
    private $security;

    public const ALLOWED_PET_PROPERTIES = [
        'name'
    ];

    public const ALLOWED_USER_PROPERTIES = [
        'name', 'Name', 'name\'s', 'Name\'s'
    ];

    public function __construct(PetRepository $petRepository, UserRepository $userRepository, Security $security)
    {
        $this->petRepository = $petRepository;
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    public function format(string $text): string
    {
        preg_match_all('/%((pet|user):[0-9]+\\.[A-Za-z\']+)%/', $text, $matches);

        $matches = $matches[1];
        $matches = array_unique($matches);

        foreach($matches as $match)
        {
            $text = $this->doReplace($text, $match);
        }

        return $text;
    }

    private function doReplace(string $text, string $match)
    {
        $parts = preg_split('/[:\\.]/', $match);

        switch($parts[0])
        {
            case 'pet': return $this->doReplacePetPart($text, '%' . $match . '%', (int)$parts[1], $parts[2]);
            case 'user': return $this->doReplaceUserPart($text, '%' . $match . '%', (int)$parts[1], $parts[2]);
            default: return $text;
        }
    }

    private function doReplacePetPart(string $text, string $match, int $petId, string $property)
    {
        if(!in_array($property, self::ALLOWED_PET_PROPERTIES))
            return $text;

        $pet = $this->petRepository->find($petId);

        return str_replace($match, $pet->{'get' . $property}(), $text);
    }

    private function doReplaceUserPart(string $text, string $match, int $userId, string $property)
    {
        if(!in_array($property, self::ALLOWED_USER_PROPERTIES))
            return $text;

        $userIsCurrentUser = $this->security->getUser() && $this->security->getUser()->getId() === $userId;

        if($userIsCurrentUser)
            $user = $this->security->getUser();
        else
            $user = $this->userRepository->find($userId);

        if($userIsCurrentUser && $property === 'name')
            return str_replace($match, 'you', $text);
        else if($userIsCurrentUser && $property === 'Name')
            return str_replace($match, 'You', $text);
        else if($property === 'name\'s')
        {
            if($userIsCurrentUser)
                return str_replace($match, 'your', $text);
            else
                return str_replace($match, $user->getName() . '\'s', $text);
        }
        else if($property === 'Name\'s')
        {
            if($userIsCurrentUser)
                return str_replace($match, 'Your', $text);
            else
                return str_replace($match, $user->getName() . '\'s', $text);
        }
        else
            return str_replace($match, $user->{'get' . $property}(), $text);
    }
}
