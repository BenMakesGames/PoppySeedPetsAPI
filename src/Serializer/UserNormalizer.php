<?php
declare(strict_types=1);

namespace App\Serializer;

use App\Entity\User;
use App\Entity\UserFollowing;
use App\Entity\UserLetter;
use App\Enum\SerializationGroupEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,

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
     * @param User $data
     */
    public function normalize($data, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalizedData = $this->normalizer->normalize($data, $format, $context);

        if(in_array(SerializationGroupEnum::MY_ACCOUNT, $context['groups']))
        {
            $normalizedData['unreadLetters'] = self::getUnreadUserLetterCount($this->em, $data);
        }

        if(in_array(SerializationGroupEnum::USER_PUBLIC_PROFILE, $context['groups']))
        {
            $friend = $this->em->getRepository(UserFollowing::class)->findOneBy([
                'user' => $this->security->getUser(),
                'following' => $data
            ]);

            if($friend)
                $normalizedData['following'] = [ 'note' => $friend->getNote() ];
        }

        return $normalizedData;
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
