<?php
declare(strict_types=1);

namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/inventory")]
class RemoveBonusController extends AbstractController
{
    /**
     * @Route("/{inventory}/removeBonus", methods={"PATCH"}, requirements={"inventory"="\d+"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function removeBonus(Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('That item does not belong to you.');

        $inventory->setEnchantment(null);

        $em->flush();

        return $responseService->success();
    }
}
