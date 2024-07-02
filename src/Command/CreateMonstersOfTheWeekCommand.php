<?php

namespace App\Command;

use App\Controller\MonsterOfTheWeek\MonsterOfTheWeekHelpers;
use App\Entity\Item;
use App\Entity\MonsterOfTheWeek;
use App\Enum\MonsterOfTheWeekEnum;
use App\Functions\ItemRepository;
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
        // population increase
        $now = new \DateTimeImmutable();

        $monsterId = $this->em->getRepository(MonsterOfTheWeek::class)
            ->createQueryBuilder('m')
            ->select('m.id')
            ->where('m.startDate <= :now')
            ->andWhere('m.endDate >= :now')
            ->setParameter('now', $now)
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
            ->getResult();

        $monsterType = $this->selectMonsterType($previousTwoMonsterTypes);

        $monster = (new MonsterOfTheWeek())
            ->setMonster($monsterType)
            ->setStartDate($now->setTime(0, 0, 0))
            ->setEndDate($now->modify('+7 days')->setTime(23, 59, 59))
            ->setCommunityTotal(0)
            ->setEasyPrize($this->selectEasyPrize($monsterType))
            ->setMediumPrize($this->selectMediumPrize($monsterType))
            ->setHardPrize($this->selectHardPrize($monsterType));

        $this->em->persist($monster);
        $this->em->flush();

        return self::SUCCESS;
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
        $this->em->getRepository(ItemRepository::class)->findOneBy([
            'name' => $possiblePrizes[array_rand($possiblePrizes)]
        ]);
    }
}
