<?php
namespace App\Service\PetActivity\Relationship;

use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Enum\RelationshipEnum;
use App\Functions\ArrayFunctions;
use Doctrine\ORM\EntityManagerInterface;

class FriendlyRivalsService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return PetActivityLog[]
     */
    public function hangOutPrivatelyAsFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        $p1Skills = [
            'knowledge of the umbra' => $p1->getPet()->getUmbra(),
            'raw strength' => $p1->getPet()->getStrength(),
            'fighting prowess' => $p1->getPet()->getBrawl(),
            'scientific knowledge' => $p1->getPet()->getScience(),
            'crafting skill' => $p1->getPet()->getCrafts(),
            'musical ability' => $p1->getPet()->getMusic(),
        ];

        $p2Skills = [
            'knowledge of the umbra' => $p2->getPet()->getUmbra(),
            'raw strength' => $p2->getPet()->getStrength(),
            'fighting prowess' => $p2->getPet()->getBrawl(),
            'scientific knowledge' => $p2->getPet()->getScience(),
            'crafting skill' => $p2->getPet()->getCrafts(),
            'musical ability' => $p2->getPet()->getMusic(),
        ];

        $combinedSkills = [
            'knowledge of the umbra' => $p1->getPet()->getUmbra() + $p2->getPet()->getUmbra(),
            'raw strength' => $p1->getPet()->getStrength() + $p2->getPet()->getStrength(),
            'fighting prowess' => $p1->getPet()->getBrawl() + $p2->getPet()->getBrawl(),
            'scientific knowledge' => $p1->getPet()->getScience() + $p2->getPet()->getScience(),
            'crafting skill' => $p1->getPet()->getCrafts() + $p2->getPet()->getCrafts(),
            'musical ability' => $p1->getPet()->getMusic() + $p2->getPet()->getMusic(),
        ];

        arsort($combinedSkills);
        $combinedSkills = array_splice($combinedSkills, 0, 3, true);

        // the pets may not compete, if they actually have different goals
        if ($p1->getRelationshipGoal() !== RelationshipEnum::FRIENDLY_RIVAL && mt_rand(1, 3) === 1)
        {
            if ($p2->getRelationshipGoal() !== RelationshipEnum::FRIENDLY_RIVAL)
                $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to brag about their ' . array_key_first($combinedSkills) . ', but realized that neither were really feeling up to it, so called the contest off.';
            else
                $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to brag about their ' . array_key_first($combinedSkills) . ', but ' . $p1->getPet()->getName() . ' wasn\'t really feeling it. ' . $p2->getPet()->getName() . ' accepted the win.';

            $p1->decrementTimeUntilChange();
            $p2->decrementTimeUntilChange();

            return $this->createLogs($message);
        }

        if ($p2->getRelationshipGoal() !== RelationshipEnum::FRIENDLY_RIVAL && mt_rand(1, 3) === 1)
        {
            if ($p1->getRelationshipGoal() !== RelationshipEnum::FRIENDLY_RIVAL)
                $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to brag about their ' . array_key_first($combinedSkills) . ', but realized that neither were really feeling up to it, so called the conetest off.';
            else
                $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to brag about their ' . array_key_first($combinedSkills) . ', but ' . $p2->getPet()->getName() . ' wasn\'t really feeling it. ' . $p1->getPet()->getName() . ' accepted the win.';

            $p1->decrementTimeUntilChange();
            $p2->decrementTimeUntilChange();

            return $this->createLogs($message);
        }

        $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to compare their accomplishments, but just ended up bickering over which types of accomplishments are even worth mentioning.';

        foreach ($combinedSkills as $description => $skill)
        {
            if (mt_rand(1, 2) === 1)
            {
                $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to brag about their ' . $description . '. ';

                $p1Roll = mt_rand(1, max(2, $p1Skills[$description] + 2));
                $p2Roll = mt_rand(1, max(2, $p2Skills[$description] + 2));

                if ($p1Roll > ceil($p2Roll * 1.25))
                {
                    $message .= $p1->getPet()->getName() . ' was clearly the more accomplished of the two! ';

                    $message .= ArrayFunctions::pick_one([
                        '(Not that ' . $p2->getPet()->getName() . ' would ever admit it!)',
                        $p2->getPet()->getName() . ' swore revenge!',
                        $p2->getPet()->getName() . ' conceded defeat... _this time!_',
                        $p2->getPet()->getName() . ' called shenanigans, demanding a rematch! The true master will be decided _next_ time!',
                    ]);
                }
                else if ($p2Roll > ceil($p1Roll * 1.25))
                {
                    $message .= $p2->getPet()->getName() . ' was clearly the more accomplished of the two! ';

                    $message .= ArrayFunctions::pick_one([
                        '(Not that ' . $p1->getPet()->getName() . ' would ever admit it!)',
                        $p1->getPet()->getName() . ' swore revenge!',
                        $p1->getPet()->getName() . ' conceded defeat... _this time!_',
                        $p1->getPet()->getName() . ' called shenanigans, demanding a rematch! The true master will be decided _next_ time!',
                    ]);
                }
                else
                {
                    $message .= ArrayFunctions::pick_one([
                        'Each claimed to be better than the other, and vowed to prove it during their next encounter!',
                        'They argued for a while about how best to test their skills, but couldn\'t come to an agreement. (Next time!)',
                        'They mocked each other\'s accomplishments, and eventually called the whole thing off without deciding on a victor.',
                    ]);
                }

                break;
            }
        }

        return $this->createLogs($message);
    }

    /**
     * @return PetActivityLog[]
     */
    private function createLogs(string $message): array
    {
        $p1Log = (new PetActivityLog())
            ->setPet($p1->getPet())
            ->setEntry($message)
            ->setIcon('icons/activity-logs/friend')
        ;

        $this->em->persist($p1Log);

        $p2Log = (new PetActivityLog())
            ->setPet($p2->getPet())
            ->setEntry($message)
            ->setIcon('icons/activity-logs/friend')
        ;

        $this->em->persist($p2Log);

        return [ $p1Log, $p2Log ];
    }

}