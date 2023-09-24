<?php
namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Entity\UserLetter;
use App\Enum\LetterSenderEnum;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\RelationshipEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\LetterRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetRepository;
use App\Repository\UserLetterRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\MuseumService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;

class LetterService
{
    private UserQuestRepository $userQuestRepository;
    private InventoryService $inventoryService;
    private ResponseService $responseService;
    private PetRepository $petRepository;
    private PetExperienceService $petExperienceService;
    private MuseumService $museumService;
    private LetterRepository $letterRepository;
    private UserLetterRepository $userLetterRepository;
    private EntityManagerInterface $em;
    private IRandom $squirrel3;

    public function __construct(
        UserQuestRepository $userQuestRepository, InventoryService $inventoryService, ResponseService $responseService,
        PetRepository $petRepository, PetExperienceService $petExperienceService, MuseumService $museumService,
        LetterRepository $letterRepository, UserLetterRepository $userLetterRepository,
        EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        $this->userQuestRepository = $userQuestRepository;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petRepository = $petRepository;
        $this->petExperienceService = $petExperienceService;
        $this->museumService = $museumService;
        $this->letterRepository = $letterRepository;
        $this->userLetterRepository = $userLetterRepository;
        $this->em = $em;
        $this->squirrel3 = $squirrel3;
    }

    public function adventure(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $log = $this->doAnniversary($petWithSkills);
        if($log) return $log;

        $log = $this->doSharuminyinka($petWithSkills);
        if($log) return $log;

        $log = $this->doKatica($petWithSkills);
        if($log) return $log;

        $log = $this->doHyssop($petWithSkills);
        if($log) return $log;

        return null;
    }

    private function doAnniversary(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $owner = $petWithSkills->getPet()->getOwner();

        $accountAgeInYears = (new \DateTimeImmutable())->diff($owner->getRegisteredOn())->y;

        if($accountAgeInYears === 0)
            return null;

        $anniversaryLettersDelivered = $this->userQuestRepository->findOneBy([
            'user' => $owner->getId(),
            'name' => 'Anniversary Letters Delivered'
        ]);

        $lettersDelivered = $anniversaryLettersDelivered ? $anniversaryLettersDelivered->getValue() : 0;

        if($lettersDelivered >= $accountAgeInYears)
            return null;

        $response = $this->doDeliverLetter($petWithSkills, LetterSenderEnum::MIA, 1);

        if(!$response)
            return null;

        if($response->letter->getLetter()->getAttachment())
            $this->museumService->forceDonateItem($owner, $response->letter->getLetter()->getAttachment(), $response->letter->getLetter()->getSender() . ' donated this to the Museum on your behalf.');

        return $response->activityLog;
    }

    private function doHyssop(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $owner = $petWithSkills->getPet()->getOwner();

        $canReceiveLettersFromFairies = $this->userQuestRepository->findOneBy([
            'user' => $owner->getId(),
            'name' => 'Can Receive Letters from Fairies',
        ]);

        if(!$canReceiveLettersFromFairies)
            return null;

        $hyssopLettersDelivered = $this->userQuestRepository->findOneBy([
            'user' => $owner->getId(),
            'name' => 'Hyssop Letters Delivered'
        ]);

        if($hyssopLettersDelivered && $hyssopLettersDelivered->getValue() >= $canReceiveLettersFromFairies->getValue())
            return null;

        $response = $this->doDeliverLetter($petWithSkills, LetterSenderEnum::HYSSOP, 4);

        return $response ? $response->activityLog : null;
    }

    private function doKatica(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $owner = $petWithSkills->getPet()->getOwner();

        if($owner->getBeehive() && $owner->getBeehive()->getWorkers() >= 5000)
        {
            $response = $this->doDeliverLetter($petWithSkills, LetterSenderEnum::KATICA, 19);

            return $response ? $response->activityLog : null;
        }

        return null;
    }

    private function doSharuminyinka(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $owner = $petWithSkills->getPet()->getOwner();

        $sharuminyinkaQuestStep = $this->userQuestRepository->findOneBy([
            'user' => $owner->getId(),
            'name' => 'Sharuminyinka\'s Despair - Step',
        ]);

        if($sharuminyinkaQuestStep && $sharuminyinkaQuestStep->getValue() === 40)
        {
            $thirtyDaysAgo = (new \DateTimeImmutable())->modify('-30 days');

            if($sharuminyinkaQuestStep->getLastUpdated() < $thirtyDaysAgo)
            {
                $response = $this->doDeliverLetter($petWithSkills, LetterSenderEnum::SHARUMINYINKA, 14);

                return $response ? $response->activityLog : null;
            }
        }

        return null;
    }

    private function doDeliverLetter(ComputedPetSkills $petWithSkills, string $sender, int $minDaysBetweenDelivery): ?LetterResponse
    {
        $pet = $petWithSkills->getPet();
        $owner = $pet->getOwner();
        $deliveryIntervalAgo = (new \DateTimeImmutable())->modify('-' . $minDaysBetweenDelivery . ' days');

        $lettersDelivered = $this->userQuestRepository->findOrCreate($owner, $sender . ' Letters Delivered', 0);

        // for letters beyond the first, we must wait at least $minDaysBetweenDelivery to deliver another message:
        if($lettersDelivered->getValue() > 0 && $lettersDelivered->getLastUpdated() >= $deliveryIntervalAgo)
            return null;

        // if all the letters have already been delivered, get outta' here:
        if($lettersDelivered->getValue() >= $this->letterRepository->getNumberOfLettersFromSender($sender))
            return null;

        $petChanges = new PetChanges($pet);

        $courier = $this->petRepository->findRandomCourier($pet);

        if($courier === null)
        {
            $descriptionForPet = 'some pet they didn\'t recognize.';
        }
        else
        {
            $courierChanges = new PetChanges($courier);
            $relationship = $pet->getRelationshipWith($courier);

            if($relationship)
            {
                switch($relationship->getCurrentRelationship())
                {
                    case RelationshipEnum::BROKE_UP:
                        $descriptionForPet = '%pet:' . $courier->getId() . '.name%! :( %pet:' . $courier->getId() . '.name% handed over the letter without saying a word, and left.';
                        $descriptionForCourier = '%pet:' . $pet->getId() . '.name%! :( %pet:' . $pet->getId() . '.name% took the letter without saying a word, and left.';
                        $pet->increaseEsteem(-$this->squirrel3->rngNextInt(4, 8));
                        $courier->increaseEsteem(-$this->squirrel3->rngNextInt(4, 8));
                        break;
                    case RelationshipEnum::DISLIKE:
                        $descriptionForPet = '%pet:' . $courier->getId() . '.name%! :| %pet:' . $courier->getId() . '.name% handed over the letter without saying a word, and left.';
                        $descriptionForCourier = '%pet:' . $pet->getId() . '.name%! :| %pet:' . $pet->getId() . '.name% took the letter without saying a word, and left.';
                        $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));
                        $courier->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));
                        break;
                    case RelationshipEnum::FRIENDLY_RIVAL:
                        $descriptionForPet = 'their friendly rival, %pet:' . $courier->getId() . '.name%! %pet:' . $courier->getId() . '.name% triumphantly handed the letter over, laughed, and left.';
                        $descriptionForCourier = '%pet:' . $pet->getId() . '.name%! %pet:' . $pet->getId() . '.name% took the letter with a smug grin! %pet:' . $courier->getId() . '.name% laughed it off, and left.';
                        break;
                    case RelationshipEnum::FRIEND:
                    case RelationshipEnum::BFF:
                    case RelationshipEnum::FWB:
                        $descriptionForPet = 'their friend, %pet:' . $courier->getId() . '.name%! %pet:' . $courier->getId() . '.name% handed the letter over, and the two chatted for a while.';
                        $descriptionForCourier = 'their friend, %pet:' . $pet->getId() . '.name%! %pet:' . $courier->getId() . '.name% handed the letter over, and the two chatted for a while.';
                        $pet->increaseLove($this->squirrel3->rngNextInt(2, 4))->increaseSafety($this->squirrel3->rngNextInt(2, 4));
                        $courier->increaseLove($this->squirrel3->rngNextInt(2, 4))->increaseSafety($this->squirrel3->rngNextInt(2, 4));
                        break;
                    case RelationshipEnum::MATE:
                        $descriptionForPet = 'their partner, %pet:' . $courier->getId() . '.name%! %pet:' . $courier->getId() . '.name% handed the letter over, and the two chatted for a while.';
                        $descriptionForCourier = 'their partner, %pet:' . $pet->getId() . '.name%! %pet:' . $courier->getId() . '.name% handed the letter over, and the two chatted for a while.';
                        $pet->increaseLove($this->squirrel3->rngNextInt(4, 8))->increaseSafety($this->squirrel3->rngNextInt(2, 4));
                        $courier->increaseLove($this->squirrel3->rngNextInt(4, 8))->increaseSafety($this->squirrel3->rngNextInt(2, 4));
                        break;
                    default:
                        throw new \Exception("Unknown relationship type \"{$relationship->getCurrentRelationship()}\"");
                }
            }
            else
            {
                $descriptionForPet = '%pet:' . $courier->getId() . '.name%.';
                $descriptionForCourier = '%pet:' . $pet->getId() . '.name%.';
            }

            $courierActivity = $this->responseService->createActivityLog($courier, '%pet:' . $courier->getId() . '.name% - on a job for Correspondence - delivered a Letter from ' . $sender . ' to ' . $descriptionForCourier, 'icons/activity-logs/letter')
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Guild', 'Mail' ]))
            ;

            $courierActivity->setChanges($courierChanges->compare($courier));
        }

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::OTHER, null);

        $activityLog = $this->responseService->createActivityLog($pet, 'While %pet:' . $pet->getId() . '.name% was thinking about what to do, a courier delivered them a Letter from ' . $sender . '! The courier was ' . $descriptionForPet, 'icons/activity-logs/letter')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Mail' ]))
        ;

        $activityLog->setChanges($petChanges->compare($pet));

        $letterDescription = '%pet:' . $pet->getId() . '.name% was delivered this letter by a courier: ' . $descriptionForPet;

        $letter = $this->giveNextLetter($owner, $sender, $letterDescription);

        $lettersDelivered->setValue($lettersDelivered->getValue() + 1);

        $response = new LetterResponse();
        $response->letter = $letter;
        $response->activityLog = $activityLog;

        return $response;
    }

    private function giveNextLetter(User $user, string $sender, string $comment): UserLetter
    {
        $existingLetters = $this->userLetterRepository->getNumberOfLettersFromSender($user, $sender);

        $nextLetter = $this->letterRepository->findBySenderIndex($sender, $existingLetters);

        if(!$nextLetter)
            throw new \InvalidArgumentException('The user already has every letter from that sender!');

        $newLetter = (new UserLetter())
            ->setUser($user)
            ->setLetter($nextLetter)
            ->setComment($comment)
        ;

        if($nextLetter->getAttachment())
        {
            $item = $this->inventoryService->receiveItem($nextLetter->getAttachment(), $user, null, 'This item was sent by ' . $sender . ', along with a letter.', LocationEnum::HOME, true);

            if($nextLetter->getBonus()) $item->setEnchantment($nextLetter->getBonus());
            if($nextLetter->getSpice()) $item->setSpice($nextLetter->getSpice());
        }

        $this->em->persist($newLetter);

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Mailbox))
            UserUnlockedFeatureHelpers::create($this->em, $user, UnlockableFeatureEnum::Mailbox);

        return $newLetter;
    }
}

class LetterResponse
{
    /** @var UserLetter */ public $letter;
    /** @var PetActivityLog */ public $activityLog;
}
