<?php
namespace App\Serializer;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Repository\UserFollowingRepository;
use App\Repository\UserLetterRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class MyAccountNormalizer implements ContextAwareNormalizerInterface
{
    private $userLetterRepository;
    private $normalizer;
    private $security;

    public function __construct(UserLetterRepository $userLetterRepository, ObjectNormalizer $normalizer, Security $security)
    {
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

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof User;
    }
}
