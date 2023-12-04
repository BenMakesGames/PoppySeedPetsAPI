<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\MeritRepository;
use App\Functions\PetActivityLogFactory;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class TalentAndExpertiseController extends AbstractController
{
    #[Route("/{pet}/pickTalent", requirements: ["pet" => "\d+"], methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function pickTalent(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new PSPPetNotFoundException();

        if($pet->getCanPickTalent() !== 'talent')
            throw new PSPInvalidOperationException('This pet is not ready to have a talent picked.');

        $talent = $request->request->get('talent', '');

        if(!in_array($talent, [ MeritEnum::MIND_OVER_MATTER, MeritEnum::MATTER_OVER_MIND, MeritEnum::MODERATION ]))
            throw new PSPFormValidationException('You gotta\' choose one of the talents!');

        $merit = MeritRepository::findOneByName($em, $talent);

        $pet->addMerit($merit);

        if($talent === MeritEnum::MIND_OVER_MATTER)
        {
            $pet->getSkills()
                ->increaseStat('intelligence')
                ->increaseStat('perception')
                ->increaseStat('dexterity')

                ->increaseStat($squirrel3->rngNextFromArray([ 'intelligence', 'perception' ]))
                ->increaseStat($squirrel3->rngNextFromArray([ 'intelligence', 'perception', 'dexterity' ]))
            ;
        }
        else if($talent === MeritEnum::MATTER_OVER_MIND)
        {
            $pet->getSkills()
                ->increaseStat('strength')
                ->increaseStat('stamina')
                ->increaseStat('dexterity')

                ->increaseStat($squirrel3->rngNextFromArray([ 'strength', 'stamina' ]))
                ->increaseStat($squirrel3->rngNextFromArray([ 'strength', 'stamina', 'dexterity' ]))
            ;
        }
        else if($talent === MeritEnum::MODERATION)
        {
            $pet->getSkills()
                ->increaseStat('strength')
                ->increaseStat('stamina')
                ->increaseStat('dexterity')
                ->increaseStat('intelligence')
                ->increaseStat('perception')
            ;
        }

        $pet->getSkills()->setTalent();

        PetActivityLogFactory::createUnreadLog($em, $pet, str_replace('%pet.name%', $pet->getName(), $merit->getDescription()))
            ->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    #[Route("/{pet}/pickExpertise", requirements: ["pet" => "\d+"], methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function pickExpertise(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new PSPPetNotFoundException();

        if($pet->getCanPickTalent() !== 'expertise')
            throw new PSPInvalidOperationException('This pet is not ready to have a talent picked.');

        $expertise = $request->request->get('expertise', '');

        if(!in_array($expertise, [ MeritEnum::FORCE_OF_WILL, MeritEnum::FORCE_OF_NATURE, MeritEnum::BALANCE ]))
            throw new PSPFormValidationException('You gotta\' choose one of the talents!');

        $merit = MeritRepository::findOneByName($em, $expertise);

        $pet->addMerit($merit);

        if($expertise === MeritEnum::FORCE_OF_WILL)
        {
            $pet->getSkills()
                ->increaseStat('intelligence')
                ->increaseStat('perception')
                ->increaseStat('dexterity')

                ->increaseStat($squirrel3->rngNextFromArray([ 'intelligence', 'perception' ]))
                ->increaseStat($squirrel3->rngNextFromArray([ 'intelligence', 'perception', 'dexterity' ]))
            ;
        }
        else if($expertise === MeritEnum::FORCE_OF_NATURE)
        {
            $pet->getSkills()
                ->increaseStat('strength')
                ->increaseStat('stamina')
                ->increaseStat('dexterity')

                ->increaseStat($squirrel3->rngNextFromArray([ 'strength', 'stamina' ]))
                ->increaseStat($squirrel3->rngNextFromArray([ 'strength', 'stamina', 'dexterity' ]))
            ;
        }
        else if($expertise === MeritEnum::BALANCE)
        {
            $pet->getSkills()
                ->increaseStat('strength')
                ->increaseStat('stamina')
                ->increaseStat('dexterity')
                ->increaseStat('intelligence')
                ->increaseStat('perception')
            ;
        }

        $pet->getSkills()->setExpertise();

        PetActivityLogFactory::createUnreadLog($em, $pet, str_replace('%pet.name%', $pet->getName(), $merit->getDescription()))
            ->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }
}
