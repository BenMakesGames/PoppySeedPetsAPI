<?php
namespace App\Enum;

final class UserStatEnum
{
    use Enum;

    const TOTAL_MONEYS_SPENT = 'Total Moneys Spent';
    const TOTAL_MONEYS_EARNED_IN_MARKET = 'Total Moneys Earned in Market';
    const ITEMS_SOLD_IN_MARKET = 'Items Sold in Market';
    const ITEMS_BOUGHT_IN_MARKET = 'Items Bought in Market';
    const FLOWERS_PURCHASED = 'Flowers Purchased';
    const COOKED_SOMETHING = 'Cooked Something';
    const ITEMS_THROWN_AWAY = 'Items Thrown Away';
    const ITEMS_DONATED_TO_MUSEUM = 'Items Donated to Museum';
    const FOOD_HOURS_FED_TO_PETS = 'Food Hours Fed to Pets';
    const PETTED_A_PET = 'Petted a Pet';
    const PRAISED_A_PET = 'Praised a Pet';
    const MONEYS_STOLEN_BY_THIEVING_MAGPIES = 'Moneys Stolen by Thieving Magpies';
    const RECIPES_LEARNED_BY_COOKING_BUDDY = 'Reciped Learned by Cooking Buddy';
    const BUGS_SQUISHED = 'Bugs Squished';
    const BUGS_PUT_OUTSIDE = 'Bugs Put Outside';
    const FERTILIZED_PLANT = 'Fertilized a Plant';
    const HARVESTED_PLANT = 'Harvested a Plant';
}