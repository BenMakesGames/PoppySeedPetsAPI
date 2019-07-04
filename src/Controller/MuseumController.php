<?php
namespace App\Controller;

use App\Entity\Item;
use App\Entity\MuseumItem;
use App\Entity\User;
use App\Enum\SerializationGroup;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\MuseumItemRepository;
use App\Service\Filter\MuseumFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/museum")
 */
class MuseumController extends PsyPetsController
{
    /**
     * @Route("/{user}/items", methods={"GET"}, requirements={"user"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function userItems(
        User $user,
        Request $request, ResponseService $responseService, MuseumFilterService $museumFilterService
    )
    {
        $museumFilterService->addRequiredFilter('user', $user->getId());

        return $responseService->success(
            $museumFilterService->getResults($request->query),
            [ SerializationGroup::FILTER_RESULTS, SerializationGroup::MUSEUM ]
        );
    }

    /**
     * @Route("/{user}/itemCount", methods={"GET"}, requirements={"user"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function userItemCount(
        User $user,
        Request $request, ResponseService $responseService, MuseumFilterService $museumFilterService
    )
    {
        $museumFilterService->addRequiredFilter('user', $user->getId());

        return $responseService->success(
            $museumFilterService->getResults($request->query),
            [ SerializationGroup::FILTER_RESULTS, SerializationGroup::MUSEUM ]
        );
    }

    /**
     * @Route("/donate", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getItems(
        ResponseService $responseService, Request $request, InventoryRepository $inventoryRepository,
        MuseumItemRepository $museumItemRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $inventoryIds = $request->request->get('inventory');

        if(is_array($inventoryIds) && count($inventoryIds) > 20)
            throw new UnprocessableEntityHttpException('You may only donate up to 20 items at a time.');

        $inventory = $inventoryRepository->findBy([ 'id' => $inventoryIds ]);

        for($i = count($inventory) - 1; $i >= 0; $i--)
        {
            if($inventory[$i]->getOwner()->getId() !== $user->getId())
            {
                unset($inventory[$i]);
                continue;
            }

            $existingItem = $museumItemRepository->findOneBy([
                'user' => $user,
                'item' => $inventory[$i]->getItem()
            ]);

            if($existingItem)
            {
                unset($inventory[$i]);
                continue;
            }
        }

        foreach($inventory as $i)
        {
            $museumItem = (new MuseumItem())
                ->setUser($user)
                ->setItem($i->getItem())
                ->setCreatedBy($i->getCreatedBy())
                ->setComments($i->getComments())
            ;

            $em->persist($museumItem);
            $em->remove($i);
        }

        $em->flush();

        return $responseService->success();
    }
}
