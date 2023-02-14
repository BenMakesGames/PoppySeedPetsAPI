<?php
namespace App\Serializer;

use App\Entity\PetSpecies;
use App\Enum\SerializationGroupEnum;
use App\Repository\PetRepository;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PetSpeciesNormalizer implements ContextAwareNormalizerInterface
{
    private $petRepository;
    private $normalizer;

    public function __construct(PetRepository $petRepository, ObjectNormalizer $normalizer)
    {
        $this->petRepository = $petRepository;
        $this->normalizer = $normalizer;
    }

    /**
     * @param PetSpecies $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if(in_array(SerializationGroupEnum::PET_ENCYCLOPEDIA, $context['groups']))
        {
            $data['numberOfPets'] = $this->petRepository->getNumberHavingSpecies($object);
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof PetSpecies;
    }
}
