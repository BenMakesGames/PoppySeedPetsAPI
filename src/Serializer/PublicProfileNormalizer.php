<?php
namespace App\Serializer;

use App\Entity\User;
use App\Enum\SerializationGroup;
use App\Repository\UserFriendRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PublicProfileNormalizer implements NormalizerInterface
{
    private $normalizer;
    private $userFriendRepository;
    private $security;

    public function __construct(UserFriendRepository $userFriendRepository, ObjectNormalizer $normalizer, Security $security)
    {
        $this->userFriendRepository = $userFriendRepository;
        $this->normalizer = $normalizer;
        $this->security = $security;
    }

    /**
     * @param User $user
     */
    public function normalize($user, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($user, $format, $context);

        if(in_array(SerializationGroup::PUBLIC_PROFILE, $context['groups']))
        {
            $friend = $this->userFriendRepository->findOneBy([
                'user' => $this->security->getUser(),
                'friend' => $user
            ]);

            if($friend)
                $data['friend'] = [ 'note' => $friend->getNote() ];
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof User;
    }
}