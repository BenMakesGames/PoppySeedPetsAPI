<?php
namespace App\Command\Traits;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Symfony\Component\Console\Question\Question;

trait AskItemTrait
{
    private ItemRepository $itemRepository;

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

            $item = $this->itemRepository->findOneBy([ 'name' => $itemName ]);
            if($item === null)
                throw new \RuntimeException('There is no Item called "' . $itemName . '".');

            return $item;
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
            $itemName = trim($itemName);

            if($itemName === '~') return null;

            $item = $this->itemRepository->findOneBy([ 'name' => $itemName ]);
            if($item === null)
                throw new \RuntimeException('There is no Item called "' . $itemName . '".');

            return $item;
        });

        return $this->ask($question);
    }
}
