<?php
declare(strict_types=1);

namespace App\Serializer;

use App\Entity\PetSpecies;
use App\Enum\SerializationGroupEnum;
use App\Repository\PetRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PetSpeciesNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,

        private readonly PetRepository $petRepository,
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
            $normalizedData['numberOfPets'] = $this->petRepository->getNumberHavingSpecies($data);
        }

        return $normalizedData;
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
