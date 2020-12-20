<?php
namespace App\Service;

use App\Repository\PetRepository;
use App\Repository\UserRepository;

class CommentFormatter
{
    private $petRepository;
    private $userRepository;

    public const ALLOWED_PROPERTIES = [
        'name'
    ];

    public function __construct(PetRepository $petRepository, UserRepository $userRepository)
    {
        $this->petRepository = $petRepository;
        $this->userRepository = $userRepository;
    }

    public function format(string $text): string
    {
        preg_match_all('/%((pet|user):[0-9]+\\.[a-z]+)%/', $text, $matches);

        $matches = $matches[1];
        array_unique($matches);

        foreach($matches as $match)
        {
            $text = $this->doReplace($text, $match);
        }

        return $text;
    }

    private function doReplace(string $text, string $match)
    {
        $parts = preg_split('/[:\\.]/', $match);

        if(!in_array($parts[2], self::ALLOWED_PROPERTIES))
            return $text;

        switch($parts[0])
        {
            case 'pet': return $this->doReplacePart($text, '%' . $match . '%', $this->petRepository->find($parts[1]), $parts[2]);
            case 'user': return $this->doReplacePart($text, '%' . $match . '%', $this->userRepository->find($parts[1]), $parts[2]);
            default: return $text;
        }
    }

    private function doReplacePart(string $text, string $match, $entity, string $property)
    {
        return str_replace($match, $entity->{'get' . $property}(), $text);
    }
}
