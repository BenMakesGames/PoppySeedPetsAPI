<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ItemRepository;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/anniversaryMuffin")
 */
class AnniversaryMuffinController extends AbstractController
{
    /**
     * @Route("/{inventory}/lengthySkillScroll", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function wishForLengthySkillScroll(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'anniversaryMuffin/#/lengthySkillScroll');

        $inventory
            ->changeItem(ItemRepository::findOneByName($em, 'Lengthy Scroll of Skill'))
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
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'anniversaryMuffin/#/museumFavor');

        $transactionService->getMuseumFavor($user, 700, 'You wished for 700 Museum Favor on a muffin! (What _is_ this game??)');

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess('You make a wish and blow out the candle... and the muffin vanishes, somehow granting you with 700 Favor in the process! (What was _in_ that muffin?!)', [ 'itemDeleted' => true ]);
    }
}
