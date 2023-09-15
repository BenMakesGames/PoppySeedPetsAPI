<?php

namespace App\Repository;

use App\Entity\DesignGoal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @method DesignGoal|null find($id, $lockMode = null, $lockVersion = null)
 * @method DesignGoal|null findOneBy(array $criteria, array $orderBy = null)
 * @method DesignGoal[]    findAll()
 * @method DesignGoal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class DesignGoalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DesignGoal::class);
    }

    /**
     * @return DesignGoal[]
     */
    public function findByIdsFromParameters(ParameterBag $params, string $fieldName): array
    {
        // *sigh* PHP...
        $designGoalIds =
            array_unique(       // unique values
                array_filter(   //   among values >= 0
                    array_map(  //     among the string values cast to integers
                        fn($id) => (int)$id,
                        $params->get($fieldName, [])
                    ),
                    fn(int $id) => $id > 0
                )
            )
        ;

        return $this->findBy([ 'id' => $designGoalIds ]);
    }
}
