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


namespace App\Repository;

use App\Entity\Letter;
use App\Enum\EnumInvalidValueException;
use App\Enum\LetterSenderEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Letter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Letter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Letter[]    findAll()
 * @method Letter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class LetterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Letter::class);
    }

    public function getNumberOfLettersFromSender(string $sender): int
    {
        if(!LetterSenderEnum::isAValue($sender))
            throw new EnumInvalidValueException(LetterSenderEnum::class, $sender);

        return (int)$this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.sender = :sender')
            ->setParameter('sender', $sender)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
