<?php
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

    protected function configure()
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
