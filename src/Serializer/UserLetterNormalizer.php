<?php
namespace App\Serializer;

use App\Entity\UserLetter;
use App\Service\CommentFormatter;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserLetterNormalizer implements ContextAwareNormalizerInterface
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
     * @param UserLetter $userLetter
     */
    public function normalize($userLetter, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($userLetter, $format, $context);

        if(array_key_exists('comment', $data))
            $data['comment'] = $this->commentFormatter->format($data['comment']);

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof UserLetter;
    }
}
