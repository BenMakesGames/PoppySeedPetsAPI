<?php
namespace App\Serializer;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Repository\UserFollowingRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PublicProfileNormalizer implements ContextAwareNormalizerInterface
{
    private $normalizer;
    private $userFollowingRepository;
    private $security;

    public function __construct(UserFollowingRepository $userFollowingRepository, ObjectNormalizer $normalizer, Security $security)
    {
        $this->userFollowingRepository = $userFollowingRepository;
        $this->normalizer = $normalizer;
        $this->security = $security;
    }

    /**
     * @param User $user
     */
    public function normalize($user, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($user, $format, $context);

        if(in_array(SerializationGroupEnum::USER_PUBLIC_PROFILE, $context['groups']))
        {
            $friend = $this->userFollowingRepository->findOneBy([
                'user' => $this->security->getUser(),
                'following' => $user
            ]);

            if($friend)
                $data['following'] = [ 'note' => $friend->getNote() ];
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof User;
    }
}
