<?php
namespace App\Controller;

use App\Annotations\DoesNotRequireHouseHours;
use App\Service\HouseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/house")
 */
class HouseController extends PoppySeedPetsController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/runHours", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function runHours(
        ResponseService $responseService, HouseService $houseService, EntityManagerInterface $em, LoggerInterface $logger
    )
    {
        $user = $this->getUser();

        try
        {
            $houseService->run($user);
            $em->flush();
        }
        catch(\Doctrine\DBAL\Driver\PDO\Exception $e)
        {
            // hide serialization deadlocks from the end-user, in this case:
            if($e->getCode() === 1213)
                $logger->warning($e->getMessage(), [ 'trace' => $e->getTraceAsString() ]);
            else
                throw $e;
        }

        return $responseService->success();
    }
}
