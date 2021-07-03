<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/anniversaryMuffin")
 */
class AnniversaryMuffinController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/lengthySkillScroll", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function wishForLengthySkillScroll(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        ItemRepository $itemRepository
    )
    {
        $this->validateInventory($inventory, 'anniversaryMuffin/#/lengthySkillScroll');

        $inventory
            ->changeItem($itemRepository->findOneByName('Lengthy Scroll of Skill'))
            ->setModifiedOn()
        ;

        $em->flush();

        $responseService->setReloadInventory(true);

        return $responseService->itemActionSuccess('You make a wish and blow out the candle... and the muffin twists itself into a Lengthy Scroll of Skill! (What was _in_ that muffin?!)', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/museumFavor", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function wishFor700MuseumFavor(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'anniversaryMuffin/#/museumFavor');

        $this->getUser()->addMuseumPoints(700);

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess('You make a wish and blow out the candle... and the muffin vanishes, somehow granting you with 700 Favor in the process! (What was _in_ that muffin?!)', [ 'itemDeleted' => true ]);
    }
}
