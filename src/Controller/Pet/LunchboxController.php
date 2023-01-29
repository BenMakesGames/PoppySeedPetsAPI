<?php
namespace App\Controller\Pet;

use App\Entity\Inventory;
use App\Entity\LunchboxItem;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class LunchboxController extends AbstractController
{
    /**
     * @Route("/{pet}/putInLunchbox/{inventory}", methods={"POST"}, requirements={"pet"="\d+", "inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function putFoodInLunchbox(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That item does not exist.');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if(!$inventory->getItem()->getFood())
            throw new UnprocessableEntityHttpException('Only foods can be placed into lunchboxes.');

        if(count($pet->getLunchboxItems()) >= 4)
            throw new UnprocessableEntityHttpException('A lunchbox cannot contain more than 4 items.');

        if($inventory->getHolder())
            throw new UnprocessableEntityHttpException($inventory->getHolder()->getName() . ' is currently holding that item!');

        if($inventory->getWearer())
            throw new UnprocessableEntityHttpException($inventory->getWearer()->getName() . ' is currently wearing that item!');

        if($inventory->getLunchboxItem())
            throw new UnprocessableEntityHttpException('That item is in ' . $inventory->getLunchboxItem()->getPet()->getName() . '\'s lunchbox!');

        $inventory
            ->setLocation(LocationEnum::LUNCHBOX)
            ->setSellPrice(null)
        ;

        $lunchboxItem = (new LunchboxItem())
            ->setPet($pet)
            ->setInventoryItem($inventory)
        ;

        $em->persist($lunchboxItem);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/takeOutOfLunchbox/{inventory}", methods={"POST"}, requirements={"pet"="\d+", "inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function takeFoodOutOfLunchbox(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That item does not exist.');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if(!$inventory->getLunchboxItem())
            throw new UnprocessableEntityHttpException('That item is not in a lunchbox! (Reload and try again?)');

        $inventory
            ->setLocation(LocationEnum::HOME)
            ->setModifiedOn()
        ;

        $em->remove($inventory->getLunchboxItem());

        $em->flush();

        return $responseService->success();
    }
}
