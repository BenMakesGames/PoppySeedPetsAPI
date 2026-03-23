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

use App\Entity\Aura;
use App\Entity\Enchantment;
use App\Entity\ItemTool;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;

class ExportAuraCommand extends PoppySeedPetsCommand
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:export-aura')
            ->setDescription('Generate SQL needed to import aura into another database.')
            ->addArgument('aura', InputArgument::REQUIRED, 'The name of the Aura to export.')
        ;
    }

    protected function doCommand(): int
    {
        if(strtolower($_SERVER['APP_ENV']) !== 'dev')
            throw new \Exception('Can only be run in dev environments.');

        $name = $this->input->getArgument('aura');
        $aura = $this->em->getRepository(Aura::class)->findOneBy([ 'name' => $name ]);

        if(!$aura)
        {
            $this->output->writeln('<error>Aura not found.</error>');
            return self::FAILURE;
        }

        echo "\n================================================================================\n";
        echo str_repeat(' ', 40 - (int)(mb_strlen($aura->getName()) / 2)) . $aura->getName() . "\n";
        echo "================================================================================\n";

        $statements = [
            $this->generateSql(Aura::class, $aura, 'the aura itself!')
        ];

        $enchantment = $this->em->getRepository(Enchantment::class)->findOneBy([ 'aura' => $aura ]);

        if($enchantment)
        {
            $enchantmentTool = $enchantment->getEffects();
            $statements[] = $this->generateSql(ItemTool::class, $enchantmentTool, 'enchantment effect');
            $statements[] = $this->generateSql(Enchantment::class, $enchantment, 'enchantment');
        }

        echo "\n" . implode("\n\n", $statements);

        $imagePath = str_contains($aura->getImage(), '/') ? substr($aura->getImage(), 0, strrpos($aura->getImage(), '/') + 1) : '';

        echo "\n\n" . 'Upload image to: https://s3.console.aws.amazon.com/s3/buckets/poppyseedpets.com?region=us-east-1&prefix=assets/images/auras/' . $imagePath . "\n\n";

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

        $encodedValues = array_map(fn($c) => self::encodeValueToSql($entity->{'get' . $entityMeta->getFieldForColumn($c)}()), $columns);

        $valueSql = implode(',', $encodedValues);

        return "-- $comment\nINSERT INTO $tableName ($columnSql) VALUES ($valueSql) ON DUPLICATE KEY UPDATE `id` = `id`;";
    }

    private static function encodeValueToSql(mixed $value): mixed
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