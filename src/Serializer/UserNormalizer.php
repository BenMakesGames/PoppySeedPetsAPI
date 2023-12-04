<?php
namespace App\Serializer;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Repository\UserFollowingRepository;
use App\Repository\UserLetterRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer implements NormalizerInterface
{
    private UserFollowingRepository $userFollowingRepository;
    private UserLetterRepository $userLetterRepository;
    private ObjectNormalizer $normalizer;
    private Security $security;

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
     * @param User $object
     */
    public function normalize($object, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if(in_array(SerializationGroupEnum::MY_ACCOUNT, $context['groups']))
        {
            $data['unreadLetters'] = $this->userLetterRepository->getNumberUnread($object);
        }

        if(in_array(SerializationGroupEnum::USER_PUBLIC_PROFILE, $context['groups']))
        {
            $friend = $this->userFollowingRepository->findOneBy([
                'user' => $this->security->getUser(),
                'following' => $object
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

    public function getSupportedTypes(?string $format): array
    {
        return [ User::class => true ];
    }
}
