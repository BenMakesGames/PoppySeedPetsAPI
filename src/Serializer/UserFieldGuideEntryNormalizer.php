<?php
namespace App\Serializer;

use App\Entity\UserFieldGuideEntry;
use App\Service\CommentFormatter;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserFieldGuideEntryNormalizer implements ContextAwareNormalizerInterface
{
    private $normalizer;
    private $commentFormatter;

    public function __construct(
        ObjectNormalizer $normalizer, CommentFormatter $commentFormatter
    )
    {
        $this->normalizer = $normalizer;
        $this->commentFormatter = $commentFormatter;
    }

    /**
     * @param UserFieldGuideEntry $petActivityLog
     */
    public function normalize($userFieldGuideEntry, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($userFieldGuideEntry, $format, $context);

        $data['comment'] = $this->commentFormatter->format($data['comment']);

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof UserFieldGuideEntry;
    }
}
