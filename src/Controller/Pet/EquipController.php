<?php
namespace App\Controller\Pet;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\EquipmentFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class EquipController extends AbstractController
{
    /**
     * @Route("/{pet}/equip/{inventory}", methods={"POST"}, requirements={"pet"="\d+", "inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function equipPet(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That item does not exist.');

        if(!$inventory->getItem()->getTool())
            throw new UnprocessableEntityHttpException('That item\'s not an equipment!');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if(!$pet->isAtHome()) throw new \InvalidArgumentException('Pets that aren\'t home cannot be interacted with.');

        if(
            $pet->hasStatusEffect(StatusEffectEnum::WEREFORM) &&
            $inventory->getItem()->getTreasure() &&
            $inventory->getItem()->getTreasure()->getSilver() > 0
        )
        {
            throw new UnprocessableEntityHttpException($pet->getName() . ' recoils at the sight of the silvery ' . $inventory->getFullItemName() . '!');
        }

        if($pet->getTool())
        {
            if($inventory->getId() === $pet->getTool()->getId())
                throw new UnprocessableEntityHttpException($pet->getName() . ' is already equipped with that ' . $pet->getTool()->getFullItemName() . '!');

            EquipmentFunctions::unequipPet($pet);
        }

        if($inventory->getHolder())
        {
            $inventory->getHolder()->setTool(null);
            $em->flush();
        }

        if($inventory->getWearer())
        {
            $inventory->getWearer()->setHat(null);
            $em->flush();
        }

        // equip the tool
        $pet->setTool($inventory);

        // move it to the wardrobe
        $inventory
            ->setLocation(LocationEnum::WARDROBE)
            ->setSellPrice(null)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/hat/{inventory}", methods={"POST"}, requirements={"pet"="\d+", "inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function hatPet(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That item does not exist.');

        if(!$inventory->getItem()->getHat())
            throw new UnprocessableEntityHttpException('That item\'s not a hat!');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if(!$pet->isAtHome()) throw new \InvalidArgumentException('Pets that aren\'t home cannot be interacted with.');

        if(!$pet->hasMerit(MeritEnum::BEHATTED))
            throw new UnprocessableEntityHttpException($pet->getName() . ' does not have the Merit required to wear hats.');

        if(
            $pet->hasStatusEffect(StatusEffectEnum::WEREFORM) &&
            $inventory->getItem()->getTreasure() &&
            $inventory->getItem()->getTreasure()->getSilver() > 0
        )
        {
            throw new UnprocessableEntityHttpException($pet->getName() . ' recoils at the sight of the silvery ' . $inventory->getFullItemName() . '!');
        }

        if($pet->getHat())
        {
            if($inventory->getId() === $pet->getHat()->getId())
                throw new UnprocessableEntityHttpException($pet->getName() . ' is already wearing that ' . $pet->getHat()->getFullItemName() . '!');

            EquipmentFunctions::unhatPet($pet);
        }

        if($inventory->getHolder())
        {
            $inventory->getHolder()->setTool(null);
            $em->flush();
        }

        if($inventory->getWearer())
        {
            $inventory->getWearer()->setHat(null);
            $em->flush();
        }

        // equip the hat
        $pet->setHat($inventory);

        // move it to the wardrobe
        $inventory
            ->setLocation(LocationEnum::WARDROBE)
            ->setSellPrice(null)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/unequip", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function unequipPet(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        if(!$pet->isAtHome()) throw new \InvalidArgumentException('Pets that aren\'t home cannot be interacted with.');

        if(!$pet->getTool())
            throw new UnprocessableEntityHttpException($pet->getName() . ' is not currently equipped.');

        EquipmentFunctions::unequipPet($pet);

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/unhat", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function unhatPet(Pet $pet, ResponseService $responseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        if(!$pet->isAtHome()) throw new \InvalidArgumentException('Pets that aren\'t home cannot be interacted with.');

        if(!$pet->getHat())
            throw new UnprocessableEntityHttpException($pet->getName() . ' is not currently wearing a hat.');

        $pet->getHat()
            ->setLocation(LocationEnum::HOME)
            ->setModifiedOn()
        ;

        $pet->setHat(null);

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }
}
