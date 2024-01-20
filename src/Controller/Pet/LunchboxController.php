<?php
namespace App\Controller\Pet;

use App\Entity\Inventory;
use App\Entity\LunchboxItem;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class LunchboxController extends AbstractController
{
    /**
     * @Route("/{pet}/putInLunchbox/{inventory}", methods={"POST"}, requirements={"pet"="\d+", "inventory"="\d+"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function putFoodInLunchbox(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('That item does not exist.');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$inventory->getItem()->getFood())
            throw new PSPInvalidOperationException('Only foods can be placed into lunchboxes.');

        if(count($pet->getLunchboxItems()) >= $pet->getLunchboxSize())
            throw new PSPInvalidOperationException($pet->getName() . '\'s lunchbox cannot contain more than ' . $pet->getLunchboxSize() . ' items.');

        if($inventory->getHolder())
            throw new PSPInvalidOperationException($inventory->getHolder()->getName() . ' is currently holding that item!');

        if($inventory->getWearer())
            throw new PSPInvalidOperationException($inventory->getWearer()->getName() . ' is currently wearing that item!');

        if($inventory->getLunchboxItem())
            throw new PSPInvalidOperationException('That item is in ' . $inventory->getLunchboxItem()->getPet()->getName() . '\'s lunchbox!');

        $inventory->setLocation(LocationEnum::LUNCHBOX);

        if($inventory->getForSale())
            $em->remove($inventory->getForSale());

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
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function takeFoodOutOfLunchbox(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('That item does not exist.');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$inventory->getLunchboxItem())
            throw new PSPInvalidOperationException('That item is not in a lunchbox! (Reload and try again?)');

        $inventory
            ->setLocation(LocationEnum::HOME)
        ;

        $em->remove($inventory->getLunchboxItem());

        $em->flush();

        return $responseService->success();
    }
}
