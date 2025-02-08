<?php
declare(strict_types=1);

namespace App\Functions;

use App\Entity\DesignGoal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class DesignGoalRepository
{
    /**
     * @return DesignGoal[]
     */
    public static function findByIdsFromParameters(EntityManagerInterface $em, ParameterBag $params, string $fieldName): array
    {
        // *sigh* PHP...
        $designGoalIds =
            array_unique(       // unique values
                array_filter(   //   among values >= 0
                    array_map(  //     among the string values cast to integers
                        fn($id) => (int)$id,
                        $params->all($fieldName)
                    ),
                    fn(int $id) => $id > 0
                )
            )
        ;

        return $em->getRepository(DesignGoal::class)->findBy([ 'id' => $designGoalIds ]);
    }
}
