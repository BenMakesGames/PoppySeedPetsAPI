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

#[Route("/item/theOpenWindow")]
class TheOpenWindowController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventoryAllowingLibrary($userAccessor->getUserOrThrow(), $inventory, 'theOpenWindow/#/read');

        return $responseService->itemActionSuccess(<<<EOMD
# The Open Window

<center>by H.H. Munro (SAKI)</center><br />

---

"My aunt will be down presently, Mr. Nuttel," said a very self-possessed young lady of fifteen; "in the meantime you must try and put up with me."

Framton Nuttel endeavored to say the correct something which should duly flatter the niece of the moment without unduly discounting the aunt that was to come. Privately he doubted more than ever whether these formal visits on a succession of total strangers would do much towards helping the nerve cure which he was supposed to be undergoing

"I know how it will be," his sister had said when he was preparing to migrate to this rural retreat; "you will bury yourself down there and not speak to a living soul, and your nerves will be worse than ever from moping. I shall just give you letters of introduction to all the people I know there. Some of them, as far as I can remember, were quite nice."

Framton wondered whether Mrs. Sappleton, the lady to whom he was presenting one of the letters of introduction came into the nice division.

"Do you know many of the people round here?" asked the niece, when she judged that they had had sufficient silent communion.

"Hardly a soul," said Framton. "My sister was staying here, at the rectory, you know, some four years ago, and she gave me letters of introduction to some of the people here."

He made the last statement in a tone of distinct regret.

"Then you know practically nothing about my aunt?" pursued the self-possessed young lady.

"Only her name and address," admitted the caller. He was wondering whether Mrs. Sappleton was in the married or widowed state. An undefinable something about the room seemed to suggest masculine habitation.

"Her great tragedy happened just three years ago," said the child; "that would be since your sister's time."

"Her tragedy?" asked Framton; somehow in this restful country spot tragedies seemed out of place.

"You may wonder why we keep that window wide open on an October afternoon," said the niece, indicating a large French window that opened on to a lawn.

"It is quite warm for the time of the year," said Framton; "but has that window got anything to do with the tragedy?"

"Out through that window, three years ago to a day, her husband and her two young brothers went off for their day's shooting. They never came back. In crossing the moor to their favorite snipe-shooting ground they were all three engulfed in a treacherous piece of bog. It had been that dreadful wet summer, you know, and places that were safe in other years gave way suddenly without warning. Their bodies were never recovered. That was the dreadful part of it." Here the child's voice lost its self-possessed note and became falteringly human. "Poor aunt always thinks that they will come back someday, they and the little brown spaniel that was lost with them, and walk in at that window just as they used to do. That is why the window is kept open every evening till it is quite dusk. Poor dear aunt, she has often told me how they went out, her husband with his white waterproof coat over his arm, and Ronnie, her youngest brother, singing 'Bertie, why do you bound?' as he always did to tease her, because she said it got on her nerves. Do you know, sometimes on still, quiet evenings like this, I almost get a creepy feeling that they will all walk in through that window--"

She broke off with a little shudder. It was a relief to Framton when the aunt bustled into the room with a whirl of apologies for being late in making her appearance.

"I hope Vera has been amusing you?" she said.

"She has been very interesting," said Framton.

"I hope you don't mind the open window," said Mrs. Sappleton briskly; "my husband and brothers will be home directly from shooting, and they always come in this way. They've been out for snipe in the marshes today, so they'll make a fine mess over my poor carpets. So like you menfolk, isn't it?"

She rattled on cheerfully about the shooting and the scarcity of birds, and the prospects for duck in the winter. To Framton it was all purely horrible. He made a desperate but only partially successful effort to turn the talk on to a less ghastly topic, he was conscious that his hostess was giving him only a fragment of her attention, and her eyes were constantly straying past him to the open window and the lawn beyond. It was certainly an unfortunate coincidence that he should have paid his visit on this tragic anniversary.

"The doctors agree in ordering me complete rest, an absence of mental excitement, and avoidance of anything in the nature of violent physical exercise," announced Framton, who labored under the tolerably widespread delusion that total strangers and chance acquaintances are hungry for the least detail of one's ailments and infirmities, their cause and cure. "On the matter of diet they are not so much in agreement," he continued.

"No?" said Mrs. Sappleton, in a voice which only replaced a yawn at the last moment. Then she suddenly brightened into alert attention--but not to what Framton was saying.

"Here they are at last!" she cried. "Just in time for tea, and don't they look as if they were muddy up to the eyes!"

Framton shivered slightly and turned towards the niece with a look intended to convey sympathetic comprehension. The child was staring out through the open window with a dazed horror in her eyes. In a chill shock of nameless fear Framton swung round in his seat and looked in the same direction.

In the deepening twilight three figures were walking across the lawn towards the window, they all carried guns under their arms, and one of them was additionally burdened with a white coat hung over his shoulders. A tired brown spaniel kept close at their heels. Noiselessly they neared the house, and then a hoarse young voice chanted out of the dusk: "I said, Bertie, why do you bound?"

Framton grabbed wildly at his stick and hat; the hall door, the gravel drive, and the front gate were dimly noted stages in his headlong retreat. A cyclist coming along the road had to run into the hedge to avoid imminent collision.

"Here we are, my dear," said the bearer of the white mackintosh, coming in through the window, "fairly muddy, but most of it's dry. Who was that who bolted out as we came up?"

"A most extraordinary man, a Mr. Nuttel," said Mrs. Sappleton; "could only talk about his illnesses, and dashed off without a word of goodby or apology when you arrived. One would think he had seen a ghost."

"I expect it was the spaniel," said the niece calmly; "he told me he had a horror of dogs. He was once hunted into a cemetery somewhere on the banks of the Ganges by a pack of pariah dogs, and had to spend the night in a newly dug grave with the creatures snarling and grinning and foaming just above him. Enough to make anyone lose their nerve."

Romance at short notice was her speciality.
EOMD);
    }
}