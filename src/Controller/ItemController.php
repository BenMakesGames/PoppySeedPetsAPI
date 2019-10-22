<?php
namespace App\Controller;

use App\Entity\Item;
use App\Enum\SerializationGroupEnum;
use App\Repository\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item")
 */
class ItemController extends PoppySeedPetsController
{
    /**
     * @Route("/{item}", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function getItem(Item $item, ResponseService $responseService, Request $request)
    {
        $this->adminIPsOnly($request);

        return $responseService->success($item, [ SerializationGroupEnum::ITEM_ADMIN ]);
    }
}
