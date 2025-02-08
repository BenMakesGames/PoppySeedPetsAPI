<?php
declare(strict_types=1);

namespace App\Serializer;

use App\Entity\User;
use App\Entity\UserFollowing;
use App\Entity\UserLetter;
use App\Enum\SerializationGroupEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $normalizer,
        private readonly Security $security,
        private readonly EntityManagerInterface $em
    )
    {
    }

    private static function getUnreadUserLetterCount(EntityManagerInterface $em, User $user)
    {
        return (int)$em->getRepository(UserLetter::class)->createQueryBuilder('ul')
            ->select('COUNT(ul.id)')
            ->andWhere('ul.user=:userId')
            ->andWhere('ul.isRead=0')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param User $object
     */
    public function normalize($object, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if(in_array(SerializationGroupEnum::MY_ACCOUNT, $context['groups']))
        {
            $data['unreadLetters'] = self::getUnreadUserLetterCount($this->em, $object);
        }

        if(in_array(SerializationGroupEnum::USER_PUBLIC_PROFILE, $context['groups']))
        {
            $friend = $this->em->getRepository(UserFollowing::class)->findOneBy([
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
