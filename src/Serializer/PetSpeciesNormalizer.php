<?php
declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Pet;
use App\Entity\PetSpecies;
use App\Enum\SerializationGroupEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PetSpeciesNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,

        private readonly EntityManagerInterface $em,
    )
    {
    }

    /**
     * @param PetSpecies $data
     */
    public function normalize($data, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalizedData = $this->normalizer->normalize($data, $format, $context);

        if(in_array(SerializationGroupEnum::PET_ENCYCLOPEDIA, $context['groups']))
        {
            $normalizedData['numberOfPets'] = self::getNumberHavingSpecies($this->em, $data);
        }

        return $normalizedData;
    }

    public static function getNumberHavingSpecies(EntityManagerInterface $em, PetSpecies $petSpecies): int
    {
        return (int)$em->getRepository(Pet::class)->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.species=:species')
            ->setParameter('species', $petSpecies)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof PetSpecies;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ PetSpecies::class => true ];
    }
}
