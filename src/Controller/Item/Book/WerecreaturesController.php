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

#[Route("/item/werecreatures")]
class WerecreaturesController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventoryAllowingLibrary($userAccessor->getUserOrThrow(), $inventory, 'werecreatures/#/read');

        return $responseService->itemActionSuccess(<<<EOBOOK
### Etymology and History

<img src="/assets/images/pets/lycanthrope/1.svg" class="float-right" width="100" />The term "lycanthrope" originates from the Greek words "lykos" (wolf) and "anthropos" (man), historically referring to the mythological concept of humans transforming into wolves.

The term "lycanthropy" has since been repurposed to denote the drastic transformation of infected animals into monstrous forms, regardless of their original species.

<img src="/assets/images/pets/lycanthrope/2.svg" class="float-right" width="100" />The earliest observations of animal lycanthropy date back to ancient civilizations. Egyptian hieroglyphics and Chinese scriptures contain descriptions of domesticated animals, particularly cats and dogs, exhibiting characteristics of larger animals. These changes were initially ascribed to divine intervention or mystical phenomena.

It wasn't until the discovery of the Hollow Earth, and the animals that live there, that lycanthropy was confirmed as a viral condition.

### Biology and Transformation

<img src="/assets/images/pets/lycanthrope/3.svg" class="float-right" width="100" />Lycanthropy is caused by the Lupine Morphological Transformation Virus (LMTV), a unique retrovirus that integrates into the host's genome and initiates a series of dramatic physiological changes.

Upon infection, the LMTV lays dormant within the host's cells until activated, typically by emotional stress. The activation of the virus triggers a complex process that causes rapid, widespread alterations in the host's body. Bones reconfigure, muscle mass significantly increases, and sensory abilities enhance.

<img src="/assets/images/pets/lycanthrope/4.svg" class="float-right" width="100" />Despite the violent nature of the transformation process, it does not lead to lasting physical harm to the lycanthrope. This is due in part to a secondary effect of the virus that grants the host an extraordinary regenerative ability, aiding recovery from transformation-related injuries.

The virus-induced transformation is temporary, with the lycanthrope reverting to their original form after a period of roughly 4-6 hours, but there have been cases where the transformation persists for days.

### Impact on Human Society

<img src="/assets/images/pets/lycanthrope/5.svg" class="float-right" width="100" />Lycanthropy is currently confined to a single island - Poppy Seed Pet Island - where the Lupine Morphological Transformation Virus (LMTV) was first identified. This unique geographical isolation has significantly shaped the island's society, animal stewardship practices, and biosecurity measures.

Pet ownership and animal stewardship on the island have evolved to accommodate the unique needs of lycanthropic animals, and island veterinarians are developing specialized knowledge and practices to ensure the health and well-being of these animals.

The island's isolation has necessitated strict biosecurity measures to prevent the spread of LMTV to other regions. No animal is allowed off the island, and humans are subject to several health checks and a quarantine period to ensure the virus remains contained to Poppy Seed Pets Island.

### Conclusion

<img src="/assets/images/pets/lycanthrope/6.svg" class="float-right" width="100" />Lycanthropy, while rooted in the ancient mythologies of human transformation, is a confirmed viral phenomenon exclusive to the animal kingdom. Confined to Poppy Seed Pets Island, the phenomenon is caused by the Lupine Morphological Transformation Virus (LMTV), which leads to dramatic physical transformations in animals.

The island's strict biosecurity measures reflect a global commitment to balancing the necessity of human travel with the importance of maintaining global biosecurity.

LMTV provides a remarkable example of the power of viruses and their ability to shape not just biological life, but also societal structures, cultural norms, and regulatory systems. Its study offers rich insights into viral evolution, zoonotic potential, and our adaptive capabilities as a society when faced with extraordinary biological phenomena.

<em>(Hm?? There's something scribbled here: "Wolf's Bane + Silver Bar + Aging Powder"...)</em>
EOBOOK);
    }
}