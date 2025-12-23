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

namespace App\Command\Traits;

use App\Entity\Item;
use App\Functions\ItemRepository;
use Symfony\Component\Console\Question\Question;

trait AskItemTrait
{
    private function askItem(string $prompt, ?Item $defaultValue): Item
    {
        if($defaultValue)
            $question = new Question($prompt . ' (' . $defaultValue->getName() . ')', $defaultValue->getName());
        else
            $question = new Question($prompt, null);

        $question->setValidator(function($itemName) {
            $itemName = trim($itemName);

            if($itemName === '' || $itemName === '~')
                throw new \RuntimeException('Must select an item.');

            return ItemRepository::findOneByName($this->em, $itemName);
        });

        return $this->ask($question);
    }

    private function askNullableItem(string $prompt, ?Item $defaultValue): ?Item
    {
        if($defaultValue)
            $question = new Question($prompt . ' (' . $defaultValue->getName() . ')', $defaultValue->getName());
        else
            $question = new Question($prompt . ' (~)', null);

        $question->setValidator(function($itemName) {
            $itemName = $itemName === null ? '~' : trim($itemName);

            if($itemName === '~') return null;

            return ItemRepository::findOneByName($this->em, $itemName);
        });

        return $this->ask($question);
    }
}
