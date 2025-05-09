<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service\PetActivity\Relationship;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Enum\RelationshipEnum;
use App\Functions\PetActivityLogFactory;
use App\Service\IRandom;
use Doctrine\ORM\EntityManagerInterface;

class FriendlyRivalsService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng
    )
    {
    }

    /**
     * @return PetActivityLog[]
     */
    public function hangOutPrivatelyAsFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        $p1WithSkills = $p1->getPet()->getComputedSkills();
        $p2WithSkills = $p2->getPet()->getComputedSkills();

        $p1Skills = [
            'knowledge of the arcane' => $p1WithSkills->getArcana()->getTotal(),
            'raw strength' => $p1WithSkills->getStrength()->getTotal(),
            'fighting prowess' => $p1WithSkills->getBrawl()->getTotal(),
            'scientific knowledge' => $p1WithSkills->getScience()->getTotal(),
            'crafting skill' => $p1WithSkills->getCrafts()->getTotal(),
            'musical ability' => $p1WithSkills->getMusic()->getTotal(),
        ];

        $p2Skills = [
            'knowledge of the arcane' => $p2WithSkills->getArcana()->getTotal(),
            'raw strength' => $p2WithSkills->getStrength()->getTotal(),
            'fighting prowess' => $p2WithSkills->getBrawl()->getTotal(),
            'scientific knowledge' => $p2WithSkills->getScience()->getTotal(),
            'crafting skill' => $p2WithSkills->getCrafts()->getTotal(),
            'musical ability' => $p2WithSkills->getMusic()->getTotal(),
        ];

        $combinedSkills = [
            'knowledge of the arcane' => $p1WithSkills->getArcana()->getTotal() + $p2WithSkills->getArcana()->getTotal(),
            'raw strength' => $p1WithSkills->getStrength()->getTotal() + $p2WithSkills->getStrength()->getTotal(),
            'fighting prowess' => $p1WithSkills->getBrawl()->getTotal() + $p2WithSkills->getBrawl()->getTotal(),
            'scientific knowledge' => $p1WithSkills->getScience()->getTotal() + $p2WithSkills->getScience()->getTotal(),
            'crafting skill' => $p1WithSkills->getCrafts()->getTotal() + $p2WithSkills->getCrafts()->getTotal(),
            'musical ability' => $p1WithSkills->getMusic()->getTotal() + $p2WithSkills->getMusic()->getTotal(),
        ];

        arsort($combinedSkills);
        $combinedSkills = array_splice($combinedSkills, 0, 3, true);

        // the pets may not compete, if they actually have different goals
        if ($p1->getRelationshipGoal() !== RelationshipEnum::FRIENDLY_RIVAL && $this->rng->rngNextInt(1, 3) === 1)
        {
            if ($p2->getRelationshipGoal() !== RelationshipEnum::FRIENDLY_RIVAL)
                $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to brag about their ' . array_key_first($combinedSkills) . ', but realized that neither were really feeling up to it, so called the contest off.';
            else
                $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to brag about their ' . array_key_first($combinedSkills) . ', but ' . $p1->getPet()->getName() . ' wasn\'t really feeling it. ' . $p2->getPet()->getName() . ' accepted the win.';

            $p1->decrementTimeUntilChange();
            $p2->decrementTimeUntilChange();

            return $this->createLogs($p1->getPet(), $p2->getPet(), $message);
        }

        if ($p2->getRelationshipGoal() !== RelationshipEnum::FRIENDLY_RIVAL && $this->rng->rngNextInt(1, 3) === 1)
        {
            if ($p1->getRelationshipGoal() !== RelationshipEnum::FRIENDLY_RIVAL)
                $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to brag about their ' . array_key_first($combinedSkills) . ', but realized that neither were really feeling up to it, so called the contest off.';
            else
                $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to brag about their ' . array_key_first($combinedSkills) . ', but ' . $p2->getPet()->getName() . ' wasn\'t really feeling it. ' . $p1->getPet()->getName() . ' accepted the win.';

            $p1->decrementTimeUntilChange();
            $p2->decrementTimeUntilChange();

            return $this->createLogs($p1->getPet(), $p2->getPet(), $message);
        }

        $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to compare their accomplishments, but just ended up bickering over which types of accomplishments are even worth mentioning.';

        foreach ($combinedSkills as $description => $skill)
        {
            if ($this->rng->rngNextInt(1, 2) === 1)
            {
                $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to brag about their ' . $description . '. ';

                $p1Roll = $this->rng->rngNextInt(1, max(2, $p1Skills[$description] + 2));
                $p2Roll = $this->rng->rngNextInt(1, max(2, $p2Skills[$description] + 2));

                if ($p1Roll > ceil($p2Roll * 1.25))
                {
                    $message .= $p1->getPet()->getName() . ' was clearly the more accomplished of the two! ';

                    $message .= $this->rng->rngNextFromArray([
                        '(Not that ' . $p2->getPet()->getName() . ' would ever admit it!)',
                        $p2->getPet()->getName() . ' swore revenge!',
                        $p2->getPet()->getName() . ' conceded defeat... _this time!_',
                        $p2->getPet()->getName() . ' called shenanigans, demanding a rematch! The true master will be decided _next_ time!',
                    ]);
                }
                else if ($p2Roll > ceil($p1Roll * 1.25))
                {
                    $message .= $p2->getPet()->getName() . ' was clearly the more accomplished of the two! ';

                    $message .= $this->rng->rngNextFromArray([
                        '(Not that ' . $p1->getPet()->getName() . ' would ever admit it!)',
                        $p1->getPet()->getName() . ' swore revenge!',
                        $p1->getPet()->getName() . ' conceded defeat... _this time!_',
                        $p1->getPet()->getName() . ' called shenanigans, demanding a rematch! The true master will be decided _next_ time!',
                    ]);
                }
                else
                {
                    $message .= $this->rng->rngNextFromArray([
                        'Each claimed to be better than the other, and vowed to prove it during their next encounter!',
                        'They argued for a while about how best to test their skills, but couldn\'t come to an agreement. (Next time!)',
                        'They mocked each other\'s accomplishments, and eventually called the whole thing off without deciding on a victor.',
                    ]);
                }

                break;
            }
        }

        return $this->createLogs($p1->getPet(), $p2->getPet(), $message);
    }

    /**
     * @return PetActivityLog[]
     */
    private function createLogs(Pet $p1, Pet $p2, string $message): array
    {
        $p1Log = PetActivityLogFactory::createUnreadLog($this->em, $p1, $message)
            ->setIcon('icons/activity-logs/friend')
        ;

        if($p1->getOwner()->getId() == $p2->getOwner()->getId())
            $p2Log = PetActivityLogFactory::createReadLog($this->em, $p2, $message);
        else
            $p2Log = PetActivityLogFactory::createUnreadLog($this->em, $p2, $message);

        $p2Log->setIcon('icons/activity-logs/friend');

        return [ $p1Log, $p2Log ];
    }
}
