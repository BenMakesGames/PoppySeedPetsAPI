<?php
namespace App\Command;

use App\Entity\Enchantment;
use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\ItemGrammar;
use App\Entity\ItemTool;
use App\Entity\ItemTreasure;
use App\Entity\Spice;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;

class ExportItemCommand extends PoppySeedPetsCommand
{
    private ItemRepository $itemRepository;
    private EntityManagerInterface $em;

    public function __construct(ItemRepository $itemRepository, EntityManagerInterface $em)
    {
        $this->itemRepository = $itemRepository;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:export-item')
            ->setDescription('Generate SQL needed to import item into another database.')
            ->addArgument('item', InputArgument::REQUIRED, 'The name of the Item to export.')
        ;
    }

    protected function doCommand(): int
    {
        if(strtolower($_SERVER['APP_ENV']) !== 'dev')
            throw new \Exception('Can only be run in dev environments.');

        $name = $this->input->getArgument('item');
        $item = $this->itemRepository->findOneBy([ 'name' => $name ]);

        if(!$item)
            throw new \Exception('There is no item by that name.');

        $treasure = $item->getTreasure();
        $tool = $item->getTool();
        $food = $item->getFood();
        $enchantment = $item->getEnchants();
        $spice = $item->getSpice();
        $grammar = $item->getGrammar();
        $plant = $item->getPlant();

        $statements = [
            'START TRANSACTION;'
        ];

        if($enchantment)
        {
            $enchantmentTool = $enchantment->getEffects();
            $statements[] = $this->generateSql(ItemTool::class, $enchantmentTool, 'enchantment effect');
            $statements[] = $this->generateSql(Enchantment::class, $enchantment, 'enchantment');
        }

        if($spice)
        {
            $spiceFood = $spice->getEffects();
            $statements[] = $this->generateSql(ItemFood::class, $spiceFood, 'spice effect');
            $statements[] = $this->generateSql(Spice::class, $spice, 'spice');
        }

        if($treasure)
            $statements[] = $this->generateSql(ItemTreasure::class, $treasure, 'treasure');

        if($tool)
            $statements[] = $this->generateSql(ItemTool::class, $tool, 'tool effect');

        if($food)
            $statements[] = $this->generateSql(ItemFood::class, $food, 'food effect');

        $statements[] = $this->generateSql(Item::class, $item, 'the item itself!');

        if($grammar)
            $statements[] = $this->generateSql(ItemGrammar::class, $grammar, 'grammar');

        $statements[] = 'COMMIT;';

        echo implode("\n\n", $statements);

        if($plant)
            echo "\n\n**********\nHEY, LISTEN! Plant data is not supported by this tool! You'll have to export that manually! (Or update this tool...)\n**********\n\n";

        return 0;
    }

    private function generateSql(string $className, object $entity, string $comment): string
    {
        $entityMeta = $this->em->getClassMetadata($className);
        $tableName = $entityMeta->getTableName();

        $columns = array_map(
            fn($row) => $row['Field'],
            $this->em->getConnection()->executeQuery("DESCRIBE `$tableName`")->fetchAllAssociative()
        );

        $columnSql = '`' . implode('`, `', $columns) . '`';

        $encodedValues = array_map(fn($c) => $this->encodeValueToSql($entity->{'get' . $entityMeta->getFieldForColumn($c)}()), $columns);

        $valueSql = implode(',', $encodedValues);

        $sql = "-- $comment\nINSERT INTO $tableName ($columnSql) VALUES ($valueSql);";

        return $sql;
    }

    private function encodeValueToSql($value)
    {
        if($value === true)
            return 1;

        if($value === false)
            return 0;

        if($value === null)
            return 'NULL';

        if(is_object($value))
            return $value->getId();

        if(is_numeric($value))
            return $value;

        return '\'' . addslashes($value) . '\'';
    }
}