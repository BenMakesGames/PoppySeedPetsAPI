<?php
declare(strict_types=1);

namespace App\Serializer;

use App\Entity\PetActivityLog;
use App\Entity\UserActivityLog;
use App\Service\CommentFormatter;
use App\Service\FlashMessage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PetActivityLogNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,

        private readonly CommentFormatter $commentFormatter
    )
    {
    }

    /**
     * @param PetActivityLog|UserActivityLog|FlashMessage $data
     */
    public function normalize($data, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalizedData = $this->normalizer->normalize($data, $format, $context);

        if(array_key_exists('entry', $normalizedData))
            $normalizedData['entry'] = $this->commentFormatter->format($normalizedData['entry']);

        return $normalizedData;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof PetActivityLog || $data instanceof UserActivityLog || $data instanceof FlashMessage;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PetActivityLog::class => true,
            UserActivityLog::class => true,
            FlashMessage::class => true
        ];
    }
}
