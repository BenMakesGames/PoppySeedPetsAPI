<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\MeritRepository;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class TalentAndExpertiseController extends AbstractController
{
    /**
     * @Route("/{pet}/pickTalent", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pickTalent(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        MeritRepository $meritRepository
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new PSPPetNotFoundException();

        if($pet->getCanPickTalent() !== 'talent')
            throw new PSPInvalidOperationException('This pet is not ready to have a talent picked.');

        $talent = $request->request->get('talent', '');

        if(!in_array($talent, [ MeritEnum::MIND_OVER_MATTER, MeritEnum::MATTER_OVER_MIND, MeritEnum::MODERATION ]))
            throw new PSPFormValidationException('You gotta\' choose one of the talents!');

        $merit = $meritRepository->deprecatedFindOneByName($talent);

        if(!$merit)
            throw new \Exception('Programmer error! The Merit "' . $talent . '" does not exist in the DB! :(');

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

        $responseService->createActivityLog($pet, str_replace('%pet.name%', $pet->getName(), $merit->getDescription()), '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/pickExpertise", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pickExpertise(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        MeritRepository $meritRepository
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new PSPPetNotFoundException();

        if($pet->getCanPickTalent() !== 'expertise')
            throw new PSPInvalidOperationException('This pet is not ready to have a talent picked.');

        $expertise = $request->request->get('expertise', '');

        if(!in_array($expertise, [ MeritEnum::FORCE_OF_WILL, MeritEnum::FORCE_OF_NATURE, MeritEnum::BALANCE ]))
            throw new PSPFormValidationException('You gotta\' choose one of the talents!');

        $merit = $meritRepository->deprecatedFindOneByName($expertise);

        if(!$merit)
            throw new \Exception('Programmer error! The Merit "' . $expertise . '" does not exist in the DB! :(');

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

        $responseService->createActivityLog($pet, str_replace('%pet.name%', $pet->getName(), $merit->getDescription()), '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }
}
