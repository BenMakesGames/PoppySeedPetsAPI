<?php
declare(strict_types=1);

namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetSpecies;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/transmigrationSerum")]
class TransmigrationSerumController extends AbstractController
{
    #[Route("/{inventory}/INJECT", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function INJECT(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'transmigrationSerum');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $speciesId = $request->request->getInt('species', 0);

        if($speciesId === 0)
            throw new PSPInvalidOperationException('A species to transmigrate to was not selected.');

        if($speciesId === $pet->getSpecies()->getId())
            throw new PSPInvalidOperationException('That\'s ' . $pet->getName() . '\'s current species! No sense in wasting the serum!');

        $newSpecies = $em->getRepository(PetSpecies::class)->find($speciesId);

        if(!$newSpecies)
            throw new PSPFormValidationException('The selected species doesn\'t exist?? Try reloading and trying again.');

        if($newSpecies->getFamily() !== $pet->getSpecies()->getFamily())
            throw new PSPInvalidOperationException($pet->getName() . ' can\'t be transmigrated into a ' . $newSpecies->getName() . '.');

        $em->remove($inventory);

        $pet->setSpecies($newSpecies);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
