<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\PetRepository;
use App\Repository\UserUnlockedAuraRepository;
use App\Service\HattierService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hattier")
 */
class HattierController extends AbstractController
{
    /**
     * @Route("/unlockedStyles", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getUnlockedAuras(HattierService $hattierService, ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        return $responseService->success(
            [
                'available' => $hattierService->getAurasAvailable($user),
            ],
            [ SerializationGroupEnum::MY_AURAS ]
        );
    }

    /**
     * @Route("/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function applyAura(
        Request $request, PetRepository $petRepository, UserUnlockedAuraRepository $userUnlockedAuraRepository,
        TransactionService $transactionService, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        $payWith = strtolower($request->request->getAlpha('payWith', 'moneys'));

        $petId = $request->request->get('pet');
        $auraId = $request->request->get('aura');

        if(!$petId || !$auraId)
            throw new PSPInvalidOperationException('A pet and style must be selected.');

        /** @var User $user */
        $user = $this->getUser();

        if($payWith === 'moneys')
        {
            if($user->getMoneys() < 200)
                throw new UnprocessableEntityHttpException('You need 200~~m~~.');
        }
        else if($payWith === 'recycling')
        {
            if($user->getRecyclePoints() < 100)
                throw new UnprocessableEntityHttpException('You need 100 recycling points.');
        }
        else
        {
            throw new PSPFormValidationException('You must choose whether to pay with moneys or with recycling points.');
        }

        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$pet->getHat())
            throw new PSPInvalidOperationException('That pet isn\'t wearing a hat!');

        $unlockedAura = $userUnlockedAuraRepository->find($auraId);

        if(!$unlockedAura || $unlockedAura->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('You haven\'t unlocked that Hattier style.');

        if($payWith === 'moneys')
            $transactionService->spendMoney($user, 200, 'Bought the ' . $unlockedAura->getAura()->getAura()->getName() . ' style from the Hattier.');
        else
            $user->increaseRecyclePoints(-100);

        $pet->getHat()->setEnchantment($unlockedAura->getAura());

        $em->flush();

        return $responseService->success();
    }
}