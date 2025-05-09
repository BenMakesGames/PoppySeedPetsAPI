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


namespace App\Command;

use App\Controller\MonsterOfTheWeek\MonsterOfTheWeekHelpers;
use App\Entity\Item;
use App\Entity\MonsterOfTheWeek;
use App\Enum\MonsterOfTheWeekEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMonstersOfTheWeekCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:create-monster-of-the-week')
            ->setDescription('Create the monster of the week, if it doesn\'t already exist.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currentMonday = (new \DateTimeImmutable())
            ->modify('tomorrow')
            ->modify('last monday')
            ->setTime(0, 0, 0);

        $monsterId = $this->em->getRepository(MonsterOfTheWeek::class)
            ->createQueryBuilder('m')
            ->select('m.id')
            ->where('m.startDate = :startDate')
            ->setParameter('startDate', $currentMonday)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if($monsterId !== null)
            return self::SUCCESS;

        $previousTwoMonsterTypesValues = $this->em->getRepository(MonsterOfTheWeek::class)
            ->createQueryBuilder('m')
            ->select('m.monster')
            ->orderBy('m.id', 'DESC')
            ->setMaxResults(2)
            ->getQuery()
            ->getSingleColumnResult();

        $previousTwoMonsterTypes = array_map(
            fn($value) => MonsterOfTheWeekEnum::from($value),
            $previousTwoMonsterTypesValues
        );

        $monsterType = $this->selectMonsterType($previousTwoMonsterTypes);

        $previousMonsterOfType = $this->getPreviousMonsterOfType($monsterType);

        if(!$previousMonsterOfType)
            $level = 155;
        else
        {
            $communityPerformanceLevel = MonsterOfTheWeekHelpers::getCommunityContributionLevel($previousMonsterOfType->getMonster(), $previousMonsterOfType->getCommunityTotal());

            // adding some random factors, so players can't totally count on specific weight.
            $add = random_int(-1, 1);
            $communityLevelWeight = random_int(572500, 622500) / 1000000; // from 0.5725 to 0.6225 (spread of 0.05, or 5%, around 60%)

            $level = (int)ceil(
                $previousMonsterOfType->getLevel() * (1 - $communityLevelWeight) +
                $communityPerformanceLevel * $communityLevelWeight
            ) + $add;

            if($level < 1)
                $level = 1;
        }

        $monster = new MonsterOfTheWeek(
            monster: $monsterType,
            startDate: $currentMonday,
            endDate: $currentMonday->modify('next sunday')->setTime(23, 59, 59),
            level: $level,
            easyPrize: $this->selectEasyPrize($monsterType),
            mediumPrize: $this->selectMediumPrize($monsterType),
            hardPrize: $this->selectHardPrize($monsterType)
        );

        $this->em->persist($monster);
        $this->em->flush();

        $output->writeln("Created level-$level {$monsterType->value}");

        return self::SUCCESS;
    }

    private function getPreviousMonsterOfType(MonsterOfTheWeekEnum $monsterType): ?MonsterOfTheWeek
    {
        return $this->em->getRepository(MonsterOfTheWeek::class)
            ->createQueryBuilder('m')
            ->where('m.monster = :monsterType')
            ->setParameter('monsterType', $monsterType)
            ->orderBy('m.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param MonsterOfTheWeekEnum[] $previousTwoMonsterTypes
     */
    private function selectMonsterType(array $previousTwoMonsterTypes): MonsterOfTheWeekEnum
    {
        $allMonsterTypes = MonsterOfTheWeekEnum::cases();
        $monsterTypes = array_filter($allMonsterTypes, fn($case) => !in_array($case, $previousTwoMonsterTypes, true));

        return $monsterTypes[array_rand($monsterTypes)];
    }

    private function selectEasyPrize(MonsterOfTheWeekEnum $monsterType): Item
    {
        return $this->selectPrizeItem(MonsterOfTheWeekHelpers::getEasyPrizes($monsterType));
    }

    private function selectMediumPrize(MonsterOfTheWeekEnum $monsterType): Item
    {
        return $this->selectPrizeItem(MonsterOfTheWeekHelpers::getMediumPrizes($monsterType));
    }

    private function selectHardPrize(MonsterOfTheWeekEnum $monsterType): Item
    {
        return $this->selectPrizeItem(MonsterOfTheWeekHelpers::getHardPrizes($monsterType));
    }

    private function selectPrizeItem(array $possiblePrizes): Item
    {
        return $this->em->getRepository(Item::class)->findOneBy([
            'name' => $possiblePrizes[array_rand($possiblePrizes)]
        ]);
    }
}
