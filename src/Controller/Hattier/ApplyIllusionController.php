<?php
namespace App\Controller\Hattier;

use App\Entity\MuseumItem;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\Clock;
use App\Service\HattierService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hattier")
 */
class ApplyIllusionController extends AbstractController
{
    /**
     * @Route("/buyIllusion", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buyIllusion(
        HattierService $hattierService, ResponseService $responseService, EntityManagerInterface $em,
        Request $request, TransactionService $transactionService, Clock $clock
    )
    {
        if($clock->getMonthAndDay() < 1000 || $clock->getMonthAndDay() >= 1100)
            throw new PSPInvalidOperationException('The Illusionist is only around during the month of October.');

        /** @var User $user */
        $user = $this->getUser();

        $payWith = strtolower($request->request->getAlpha('payWith', 'moneys'));

        if($payWith === 'moneys')
        {
            if($user->getMoneys() < 200)
                throw new PSPNotEnoughCurrencyException('200~~m~~', $user->getMoneys() . '~~m~~');
        }
        else if($payWith === 'recycling')
        {
            if($user->getRecyclePoints() < 100)
                throw new PSPNotEnoughCurrencyException('100♺', $user->getRecyclePoints() . '♺');
        }
        else
        {
            throw new PSPFormValidationException('You must choose whether to pay with moneys or with recycling points.');
        }

        $petId = $request->query->getInt('petId');
        $illusionId = $request->query->getInt('illusionId');

        $pet = $em->getRepository(Pet::class)->findOneBy([
            'id' => $petId,
            'owner' => $user,
        ]);

        if(!$pet)
            throw new PSPPetNotFoundException();

        if(!$pet->getTool())
            throw new PSPInvalidOperationException('This pet does not have a tool equipped.');

        // verify that the user has donated the illusionId in question
        $donation = $em->getRepository(MuseumItem::class)->findOneBy([
            'user' => $user,
            'item' => $illusionId,
        ]);

        if(!$donation)
            throw new PSPNotFoundException('You have not donated one of those to the Museum...');

        if($payWith === 'moneys')
            $transactionService->spendMoney($user, 200, 'Bought an illusion from the Hattier.', true, [ 'Hattier' ]);
        else
            $transactionService->spendRecyclingPoints($user, 100, 'Bought an illusion from the Hattier.', [ 'Hattier' ]);

        $pet->getTool()->setIllusion($donation->getItem());

        return $responseService->success(
            [
                'available' => $hattierService->getAurasAvailable($user),
            ],
            [ SerializationGroupEnum::MY_AURAS ]
        );
    }
}