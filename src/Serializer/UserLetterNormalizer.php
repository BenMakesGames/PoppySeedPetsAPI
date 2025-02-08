<?php
declare(strict_types=1);

namespace App\Serializer;

use App\Entity\UserLetter;
use App\Service\CommentFormatter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserLetterNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $normalizer,
        private readonly CommentFormatter $commentFormatter
    )
    {
    }

    /**
     * @param UserLetter $object
     */
    public function normalize($object, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if(array_key_exists('comment', $data))
            $data['comment'] = $this->commentFormatter->format($data['comment']);

        return $data;
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
