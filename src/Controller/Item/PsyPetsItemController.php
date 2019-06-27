<?php
namespace App\Controller\Item;

use App\Controller\PsyPetsController;
use App\Entity\Inventory;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

abstract class PsyPetsItemController extends PsyPetsController
{
    protected function validateInventory(Inventory $inventory, string $action)
    {
        if(!$this->getUser() || $this->getUser()->getId() !== $inventory->getOwner()->getId() || !$inventory->getItem()->hasUseAction($action))
            throw new UnprocessableEntityHttpException('That item no longer exists, or cannot be used in that way.');
    }
}