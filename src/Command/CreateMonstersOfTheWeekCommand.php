<?php

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

        $previousTwoMonsterTypes = $this->em->getRepository(MonsterOfTheWeek::class)
            ->createQueryBuilder('m')
            ->select('m.monster')
            ->orderBy('m.id', 'DESC')
            ->setMaxResults(2)
            ->getQuery()
            ->getSingleColumnResult();

        $monsterType = $this->selectMonsterType($previousTwoMonsterTypes);

        $previousMonsterOfType = $this->getPreviousMonsterOfType($monsterType);

        if(!$previousMonsterOfType)
            $level = 155;
        else
        {
            $communityPerformanceLevel = MonsterOfTheWeekHelpers::getCommunityContributionLevel($previousMonsterOfType->getMonster(), $previousMonsterOfType->getCommunityTotal());

            $add = random_int(-1, 1);
            $communityLevelWeight = 0.591893;

            $level = ceil(
                $previousMonsterOfType->getLevel() * (1 - $communityLevelWeight) +
                $communityPerformanceLevel * $communityLevelWeight
            ) + $add;

            if($level < 1)
                $level = 1;
        }

        $monster = (new MonsterOfTheWeek())
            ->setMonster($monsterType)
            ->setStartDate($currentMonday)
            ->setEndDate($currentMonday->modify('next sunday')->setTime(23, 59, 59))
            ->setCommunityTotal(0)
            ->setLevel($level)
            ->setEasyPrize($this->selectEasyPrize($monsterType))
            ->setMediumPrize($this->selectMediumPrize($monsterType))
            ->setHardPrize($this->selectHardPrize($monsterType));

        $this->em->persist($monster);
        $this->em->flush();

        $output->writeln("Created level-$level $monsterType");

        return self::SUCCESS;
    }

    private function getPreviousMonsterOfType(string $monsterType): ?MonsterOfTheWeek
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

    private function selectMonsterType(array $previousTwoMonsterTypes): string
    {
        $allMonsterTypes = MonsterOfTheWeekEnum::getValues();
        $monsterTypes = array_diff($allMonsterTypes, $previousTwoMonsterTypes);

        return $monsterTypes[array_rand($monsterTypes)];
    }

    private function selectEasyPrize(string $monsterType)
    {
        return $this->selectPrizeItem(MonsterOfTheWeekHelpers::getEasyPrizes($monsterType));
    }

    private function selectMediumPrize(string $monsterType)
    {
        return $this->selectPrizeItem(MonsterOfTheWeekHelpers::getMediumPrizes($monsterType));
    }

    private function selectHardPrize(string $monsterType)
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
