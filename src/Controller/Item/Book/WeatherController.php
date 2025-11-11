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


namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/weatherGuide")]
class WeatherController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventoryAllowingLibrary($userAccessor->getUserOrThrow(), $inventory, 'weatherGuide/#/read');

        return $responseService->itemActionSuccess(<<<EOMD
# Poppy Seed Pets Island Skies: A Guide to Our Weather

### Importance of Understanding Local Weather

Understanding local weather is crucial for planning your daily activities. Whether you're commuting to and from HERG research facilities, participating in fieldwork, or exploring the island's natural beauty, knowing the weather forecast helps ensure your safety and efficiency. It can mean the difference between being caught in an unexpected storm and completing your day's work unimpeded.

Poppy Seed Pets Island's unique geographical location makes it susceptible to a range of weather phenomena, from sudden rainstorms to more severe weather events. For those new to the island, being aware of what weather to expect and how to prepare for it is vital. This knowledge is not only a matter of convenience but of safety. The research facility prioritizes the well-being of its staff and residents, providing weather updates and preparedness guidelines tailored to our specific needs.

### Seasonal Patterns

Nestled in the warm waters of the Indian Ocean, Poppy Seed Pets Island enjoys warm weather year-round, with average temperatures rarely dipping below 22°C or rising above 34°C. This temperate backdrop sets the stage for the island's distinct seasonal rhythms: wet and dry seasons which are marked by rapidly changing weather conditions. These shifts require flexibility and adaptability, as weather patterns can alter unexpectedly.

#### The Wet Season (November to April)

This period sees the majority of the island's annual rainfall, driven by the southwest monsoon. Rain is often heavy and can last for hours or even days.

#### The Dry Season (May to October)

While the change is subtle, the dry season is generally cooler, especially during the evenings and early mornings, making it more comfortable for those unaccustomed to tropical climates.

### Weather's Influence on Wildlife

Weather conditions play a crucial role in the daily activities and survival strategies of wildlife, affecting feeding patterns, movement, reproduction, and visibility. Many species have developed specific adaptations to cope with the tropical climate and its seasonal extremes.

#### Frogs

Frogs and other amphibians are more visible and active during the rainy season, as the moisture is crucial for their breeding and survival.

#### Birds

Many bird species take cover during heavy rains, though the period just after rain may see increased activity as they search for food.

Dry conditions may lead to more birds congregating around diminishing water sources, making them easier to spot.

#### Insects

The wet season sees a surge in insect populations, including mosquitoes and butterflies, which can affect the behavior of insectivorous animals.

#### Large Mammals

Larger mammals may be less visible during continuous rain, seeking shelter to stay warm and dry; however, the lush vegetation post-rain provides excellent feeding opportunities.

During the transitional periods between seasons, sudden weather changes can lead to dramatic shifts in animal behavior. Predators may take advantage of these shifts to hunt unsuspecting prey.

### Understanding Severe Weather Risks

Poppy Seed Pets Island, with its tropical climate, is prone to severe weather events. These can include heavy rains, strong winds, and occasionally, cyclones. Understanding these risks is essential for safety and preparedness. HERG continuously monitors weather patterns and provides timely updates to keep everyone informed of potential severe weather.

#### Preparing for Severe Weather

* Residents should maintain an emergency kit containing essentials such as water, non-perishable food, a first aid kit, flashlights, extra batteries, and important documents in waterproof containers.
* Ensure that your living and working spaces are secure. This includes checking the stability of structures, securing loose items that could become projectiles in high winds, and ensuring proper drainage around facilities to reduce flooding risks.
* Establish a communication plan with your team and loved ones. Know how to get in contact with each other during a severe weather event, and make sure everyone knows the plan.

### Staying Informed and Connected

As we've explored throughout this guide, understanding the unique weather patterns of Poppy Seed Pets Island is essential for the safety, well-being, and success of all who live and work here. By staying informed about our local climate, you are better equipped to navigate daily challenges and appreciate everything the island offers.

Should you have any questions or require further information, please do not hesitate to contact HERG's meteorological department.

Stay curious. Stay safe. Stay connected.
EOMD);
    }
}