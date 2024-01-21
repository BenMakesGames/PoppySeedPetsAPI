<?php
namespace App\Command;

use App\Entity\Enchantment;
use App\Entity\HollowEarthTileCard;
use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\ItemGrammar;
use App\Entity\ItemGroup;
use App\Entity\ItemHat;
use App\Entity\ItemTool;
use App\Entity\ItemTreasure;
use App\Entity\Plant;
use App\Entity\PlantYield;
use App\Entity\PlantYieldItem;
use App\Entity\Spice;
use App\Functions\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;

class ExportItemCommand extends PoppySeedPetsCommand
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
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
        $item = ItemRepository::findOneByName($this->em, $name);

        echo "\n================================================================================\n";
        echo str_repeat(' ', 40 - mb_strlen($item->getName()) / 2) . $item->getName() . "\n";
        echo "================================================================================\n";

        $groups = $item->getItemGroups();
        $treasure = $item->getTreasure();
        $tool = $item->getTool();
        $hat = $item->getHat();
        $food = $item->getFood();
        $enchantment = $item->getEnchants();
        $spice = $item->getSpice();
        $grammar = $item->getGrammar();
        $plant = $item->getPlant();
        $tile = $item->getHollowEarthTileCard();

        $statements = [];

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

        if($hat)
            $statements[] = $this->generateSql(ItemHat::class, $hat, 'hat');

        if($food)
            $statements[] = $this->generateSql(ItemFood::class, $food, 'food effect');

        if($plant)
        {
            $statements[] = $this->generateSql(Plant::class, $plant, 'plant');

            foreach($plant->getPlantYields() as $yield)
            {
                $statements[] = $this->generateSql(PlantYield::class, $yield, 'plant yield');

                foreach($yield->getItems() as $yieldItem)
                    $statements[] = $this->generateSql(PlantYieldItem::class, $yieldItem, 'plant yield item');
            }
        }

        if($tile)
            $statements[] = $this->generateSql(HollowEarthTileCard::class, $tile, 'hollow earth tile card');

        $statements[] = $this->generateSql(Item::class, $item, 'the item itself!');

        if($grammar)
            $statements[] = $this->generateSql(ItemGrammar::class, $grammar, 'grammar');

        if(count($groups) > 0)
        {
            $sql = "-- item groups\nINSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES ";

            $valueSqls = array_map(
                fn(ItemGroup $group) => '(' . $group->getId() . ', ' . $item->getId() . ')',
                $groups->toArray()
            );

            $sql .= join(', ', $valueSqls) . ' ON DUPLICATE KEY UPDATE `id` = `id`;';

            $statements[] = $sql;
        }

        echo "\n" . implode("\n\n", $statements);

        if($item->getTool()->getWhenGather()?->getId() == $item->getId() || $item->getTool()->getWhenGatherAlsoGather()?->getId() == $item->getId())
        {
            echo "\n\n********************************************************************************\nWARNING: There is a circular reference in the tool effect. You will need to manually fix the SQL to handle this.\n********************************************************************************";
        }

        $image = substr($item->getImage(), 0, strrpos($item->getImage(), '/') + 1);

        echo "\n\n" . 'Upload image to: https://s3.console.aws.amazon.com/s3/buckets/poppyseedpets.com?region=us-east-1&prefix=assets/images/items/' . $image . "\n\n";

        echo "================================================================================\n\n";

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

        $encodedValues = array_map(fn($c) => ExportItemCommand::encodeValueToSql($entity->{'get' . $entityMeta->getFieldForColumn($c)}()), $columns);

        $valueSql = implode(',', $encodedValues);

        $sql = "-- $comment\nINSERT IGNORE INTO $tableName ($columnSql) VALUES ($valueSql) ON DUPLICATE KEY UPDATE `id` = `id`;";

        return $sql;
    }

    private static function encodeValueToSql($value)
    {
        if($value === true)
            return 1;

        if($value === false)
            return 0;

        if($value === null)
            return 'NULL';

        if(is_array($value))
            return '"' . addslashes(json_encode($value)) . '"';

        if(is_object($value))
            return $value->getId();

        if(is_numeric($value))
            return $value;

        return '"' . addslashes($value) . '"';
    }
}