<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service;

use App\Entity\Pet;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CommentFormatter
{
    public const array AllowedPetProperties = [
        'name'
    ];

    public const array AllowedUserProperties = [
        'name', 'Name', 'name\'s', 'Name\'s'
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserAccessor $userAccessor
    )
    {
    }

    /**
     * @return string[]
     */
    public function getUserIds(string $text): array
    {
        preg_match_all('/%user:([0-9]+)\\.[A-Za-z\']+%/', $text, $matches);

        $matches = $matches[1];
        $matches = array_unique($matches);

        return $matches;
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

    private function doReplace(string $text, string $match): string
    {
        $parts = preg_split('/[:\\.]/', $match);

        return match ($parts[0])
        {
            'pet' => $this->doReplacePetPart($text, '%' . $match . '%', (int)$parts[1], $parts[2]),
            'user' => $this->doReplaceUserPart($text, '%' . $match . '%', (int)$parts[1], $parts[2]),
            default => $text,
        };
    }

    private function doReplacePetPart(string $text, string $match, int $petId, string $property): string
    {
        if(!in_array($property, self::AllowedPetProperties))
            return $text;

        $pet = $this->em->getRepository(Pet::class)->find($petId);

        return str_replace($match, $pet->{'get' . $property}(), $text);
    }

    private function doReplaceUserPart(string $text, string $match, int $userId, string $property): string
    {
        if(!in_array($property, self::AllowedUserProperties))
            return $text;

        $userIsCurrentUser = $this->userAccessor->getUser() && $this->userAccessor->getUser()->getId() === $userId;

        if($userIsCurrentUser)
            $user = $this->userAccessor->getUser();
        else
            $user = $this->em->getRepository(User::class)->find($userId);

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
