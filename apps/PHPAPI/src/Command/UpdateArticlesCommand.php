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

use App\Entity\ItemGrammar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;

class UpdateArticlesCommand extends PoppySeedPetsCommand
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
            ->setName('app:update-articles')
            ->setDescription('Allows updating item articles relatively quickly...')
            ->addArgument('start', InputArgument::OPTIONAL, 'Item id to start with.')
        ;
    }

    protected function doCommand(): int
    {
        $firstId = (int)$this->input->getArgument('start');

        /** @var ItemGrammar[] $itemGrammars */
        $itemGrammars = $this->em->getRepository(ItemGrammar::class)->createQueryBuilder('i')
            ->andWhere('i.id>:firstId')
            ->setParameter('firstId', $firstId)
            ->addOrderBy('i.item', 'ASC')
            ->getQuery()
            ->execute()
        ;

        foreach($itemGrammars as $grammar)
        {
            $this->output->writeln('#' . $grammar->getItem()->getId() . ' ' . $grammar->getItem()->getName());
            $grammar->setArticle($this->askString('Article: ', $grammar->getArticle()));
            $this->em->flush();
        }

        return self::SUCCESS;
    }
}
