<?php
declare(strict_types=1);

namespace App\Serializer;

use App\Entity\UserLetter;
use App\Service\CommentFormatter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserLetterNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,

        private readonly CommentFormatter $commentFormatter
    )
    {
    }

    /**
     * @param UserLetter $data
     */
    public function normalize($data, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalizedData = $this->normalizer->normalize($data, $format, $context);

        if(array_key_exists('comment', $normalizedData))
            $normalizedData['comment'] = $this->commentFormatter->format($normalizedData['comment']);

        return $normalizedData;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof UserLetter;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ UserLetter::class => true ];
    }
}
