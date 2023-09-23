<?php
namespace App\Serializer;

use App\Entity\PetActivityLog;
use App\Entity\UserActivityLog;
use App\Enum\SerializationGroupEnum;
use App\Service\CommentFormatter;
use App\Service\FlashMessage;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PetActivityLogNormalizer implements ContextAwareNormalizerInterface
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
     * @param PetActivityLog|UserActivityLog|FlashMessage $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if(array_key_exists('entry', $data))
            $data['entry'] = $this->commentFormatter->format($data['entry']);

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof PetActivityLog || $data instanceof UserActivityLog || $data instanceof FlashMessage;
    }
}
