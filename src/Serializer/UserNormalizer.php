<?php
namespace App\Serializer;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Repository\UserFollowingRepository;
use App\Repository\UserLetterRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer implements ContextAwareNormalizerInterface
{
    private $userFollowingRepository;
    private $userLetterRepository;
    private $normalizer;
    private $security;

    public function __construct(
        UserLetterRepository $userLetterRepository, ObjectNormalizer $normalizer, Security $security,
        UserFollowingRepository $userFollowingRepository
    )
    {
        $this->userFollowingRepository = $userFollowingRepository;
        $this->userLetterRepository = $userLetterRepository;
        $this->normalizer = $normalizer;
        $this->security = $security;
    }

    /**
     * @param User $user
     */
    public function normalize($user, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($user, $format, $context);

        if(in_array(SerializationGroupEnum::MY_ACCOUNT, $context['groups']))
        {
            $data['unreadLetters'] = $this->userLetterRepository->getNumberUnread($user);
        }

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

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof User;
    }
}
