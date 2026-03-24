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
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/schoolOfJelly")]
class TheSchoolOfJellyController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventoryAllowingLibrary($user, $inventory, 'schoolOfJelly/#/upload');

        if(!$user->getCookingBuddy())
            $message = 'You need a Cooking Buddy to do this.';
        else
            $message = 'Your Cooking Buddy tried reading this book, but could make neither heads nor tayles of the Old Engliſh grammar and ſpellings.';

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventoryAllowingLibrary($userAccessor->getUserOrThrow(), $inventory, 'schoolOfJelly/#/read');

        return $responseService->itemActionSuccess(<<<EOD
<center><b>The School of Jelly</b><br>Reduced into RULES of PRACTICE.<br>Being the Tranſlation of the <cite>French L'Eſcoles des gelée</cite>.<br><br>Anno, 1680</center>

_Francene._ Good Morrow, Katy.

_Katy._ Oh! Good Morrow Couſin, and what good Wind blows you hither, now my Mother is from home, Lord how glad am I to ſee you, is this Viſit pure kindeſs or buſineſs?

_Francene._ No busſineſs, I aſſure you, but pure affection, I am come to chat and talk with you, 'tis weariſome being alone, and methinks, 'tis an age ſince laſt I ſaw you.

_Katy._ You ſay true, and I am much obliged to you, will you pleaſe to ſit down, you find no body at home but me and the Maid.

_Francene._ I think you do nothing elſe, you live here confined to your Chamber, as if it were a Nunnery; you never ſtir abroad, and ſeldom injoy rare ſweetmeats.

_Katy._ You ſay very true, Couſin, what ſhould I trouble my ſelf with any ſweetmeats; my Mother tells me it is beſt to eat ſimply.

_Francene._ Thou art finely fitted indeed with a Mother, who ought now to take care to pleaſe thee, as formerly ſhe did herſelf, what's become of Parents love and affection now adays. What an ignorant innocent Fool art thou?

_Katy._ Pray Couſin, why do you ſay ſo, is there anything to be learned, which I do not know?

_Francene._ You are ſo ignorant, you are to learn every thing.

_Katy._ Sweet Couſin, I conſeſs my ignorance, in which I am likely to continue, unleſs you will pleaſe to explain it unto me.

_Francene._ Alas Child, I can but pity thee, and thy misfortune, for there is food that you have not yet taſted of, which as much exceeds all the reſt, as Wine doth fair water, the moſt ſoverain pleaſure we poor Mortals injoy.

_Katy._ Dear Couſin. expound your ſelf more clearly unto me, I underſtand not in the leaſt what all this diſcourſe tends to, tell me therefore in plain Engliſh, what muſt I do to attain this pleaſure?

_Francene._ Why then in ſhort, 'tis this, a young Maid can without any cost or trouble make the moſt pleaſurable ſweetmeats imaginable.

_Katy._ Oh, good Couſin, what a mind have I to know what these ſweetmeats are, and how to make them.

_Francene._ Be not to haſty and you ſhall know all, did you never ſee a jelly?

_Katy._ I never ſaw a jelly in my life, I have ſeen ſmall green patches in the ſea?

_Francene._ No, that will not do, these ſmall patcheſ which the Wiſe of uſ call Algae, you muſt prepare into Agar-Agar before ſeeing any jelly.

_Katy._ If it muſt be ſo prepared truly then I never ſaw any.

_Francene._ Dear Couſin, I love thee too well to keep thee longer in ignorance, did you never ſee a man drinking Coffee Bean Tea?

_Katy._ Yes once I saw a man drinking, he ſeeing me look at him turned himſelf towards me, and the thing he had in his hand, appeared to be like a cup of dark liquid, which I thought by ſmell to be of Coffee Beans.

_Francene._ I am juſt now going to tell you things which will ſeem a great deal more ſtrange unto you.

_Katy._ You oblige me infinitely.

_Francene._ The tea wants ſugar, and after being fully mixt, muſt you add some of the Agar-Agar which you call green patches.

_Katy._ I very well apprehend what you ſay, but to what purpoſe has tea all theſe things?

_Francene._ Yes marry does it, for it is this very thing which giveth the delight I all this while have been talking of. For when the Agar-Agar has been added, and the mixture cools, it becomes a Coffee Jelly, which is the greateſt pleaſure imaginable.

_Katy._ Lord Couſin, what ſtrange things do you tell me. But ſtill I am not ſatisfied, ſure there is more to be done with the Agar-Agar?

_Francene._ Liſten then. Blueberries, Apricots, Pamplemouſse and other like fruitſ may inſtead of Coffee Bean Tea be of uſe, but be ſure you include ſugar, the natural sweetneſs of fruit being inſufficient.

_Katy._ Pray Couſin, ſince you have taken the pains to inſtruct me thus far, leave me not in any ignorance, and therefore inform me what else may be done with the Agar-Agar.

_Francene._ Yes, another, for even the Worms of the Earth may be uſed to create a flavourſome ſweetmeat.

_Katy._ That's a new way, it ſeems this pleaſure ha's many forms.

_Francene._ Yes, above a Hundred, have you but a little patience and I will tell you them all.

_Katy._ My fancy is ſo extreamly raiſed by your very telling me, that I am almoſt mad to be at it.

_Francene._ I have a great deal more to tell you, but let us make no more haſt then good ſpeed, for by a little and a little you will ſoon learn all.

_Katy._ I am very well ſatisfied, but methinks I would fain know what makes my Mouth water ſo that I cannot take any reſt for tumbling and toſſing, pray can you tell me what will prevent it?

_Francene._ You muſt get you leafes of Mint, and cruſh it with the ſugar.

_Katy._ How ſay you with leafes? I cannot imagine how that can be?

_Francene._ Yes with leafes of Mint, and with good uſe of the Agar-Agar, this will produce Graſs Jelly. 

_Katy._ I'le be ſure not to forget this way you tell me of; but did not you tell me you ſometimes received a great deal of jelly from ſome one?

_Francene._ Yes marry did I, I have a Friend in a corner, who prepares jellies for me as often as I have a mind to it, and I love him extreamly for it.

_Katy._ Truly he deſerves it if he pleaſeth you ſo much, but is your pleaſure and ſatisfaction ſo great?

_Francene._ I tell you, I am ſometimes beſides my ſelf.

_Katy._ But how ſhall I get such a Friend? Do you know any body I could truſt in an affair of this nature?

_Francene._ I cannot pitch upon any whom I think fit for you.

_Katy._ Lord Couſin, what a happy Woman are you, and what a great deal of time have I already loſt, but pray tell me, how muſt I play my Cards, for without your aſſiſtance I ſhall never attain to what I ſo much deſire.

_Francene._ I'le endeavor to help you out of the mire, but you muſt frankly tell me, which fruit you most eſteem.

_Katy._ To be ingenious then, I love melon beſt.

_Francene._ Then reſolve to think of no fruit elſe but Melowatern and Honeydont, theſe too may be mixt with ſugar and Agar-Agar, to ſo much delight.

_Katy._ For the patience you have had all this while to inſtruct my thick ſoul in all theſe jelly leſſons, and of thoſe most excellent reaſons you give for every thing, making me perceive what an inexhaſtible Fountain Agar-Agar is, this I am ſure of, I never could have had a better informer to inſtruct me from it's firſt Rudiments, to it's higheſt notions imaginable.

_Francene._ Pray no more of your compliments, love hath this excellency in it, that it entirely ſatisfyeth every body, according to their apprehenſions, the moſt ignorant receiving pleaſure though they know not what to call it, hence it comes, that the more expert and refined wits have a double ſhare of it's delights, in the ſoft and ſweet imaginations of the tongue, and now it comes in my mind, I like this way of the Coffee Jelly beyond any other, becauſe the ſharpneſs of the coffee adds fewel to fire the ſenſes, that one might almoſt expire with delight. This is a Subject one might amply enlarge on if there were time.

_Katy._ 'Tis impoſſible to repreſent every bodies imagination upon this ſubject, for methinks I could invent more flavours then you have told me of, and as pleaſing unto me, but pray whilſt you are putting on your Scarfe to be gone tell me one thing more.

_Francene._ Well, what is it?

_Katy._ How do I make Coffee Bean Tea?

_Francene._ Truly, that will require little more time then the putting on of my Scafe, so I will do this, provided, when I have done you will keep me no longer.

_Katy._ Let me have your inſtructions, who are ſo great a Miſtriſs in the art of jelly.

_Francene._ Take no more than ſome Coffee Beans, preparing them directly, and from this you will produce enough for two cups of Coffee Bean Tea, and in following my other inſtructions of which you ſurely must remember, two Coffee Jellies.

_Katy._ Theſe lectures Couſin, which you read unto me are far different from thoſe my Mother Preaches, they treat of nothing but vertue and health.

_Francene._ Yes, yes, Couſin, ſo goes the World now adays; lyes overcome truth, reaſon and experience, and ſome fooliſh empty ſayings are better approved of then real pleaſures. There is nothing ſweeter than to prepare jelly; Agar-Agar and ſugar are the chief actors in the Myſtery of Sweetmeats, but I have ſaid enough for once, and muſt not now pretend to reform the World, ſome are wiſer than ſome.

_Katy._ Your Doctrine is admirable, let every body live as they pleaſe for me, methinks there is nothing ſo pleaſing as jellies, and the Minuts we ſpend eating them are the ſweeteſt and moſt pleaſant of our life. Be well till I ſee you again.

_Francene._ And you too. Adieu, adieu.
EOD);
    }
}
