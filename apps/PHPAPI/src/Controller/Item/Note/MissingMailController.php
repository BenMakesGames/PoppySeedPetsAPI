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

namespace App\Controller\Item\Note;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Functions\ItemRepository;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route("item/missingMail")]
class MissingMailController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    public function read(
        Inventory $inventory, UserAccessor $userAccessor, ResponseService $responseService,
        IRandom $rng, EntityManagerInterface $em
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'missingMail/#/read');

        $inventory
            ->changeItem(ItemRepository::findOneByName($em, 'Paper'))
            ->addComment('Once a piece of Missing Mail. As it was read the words vanished leaving behind this blank sheet of Paper.')
        ;

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($rng->rngNextFromArray(self::Letters), [ 'itemDeleted' => true ]);
    }

    private const array Letters = [
        <<<EOT
        **PINEWOOD ESTATES HOMEOWNERS ASSOCIATION**<br>
        Maintaining Property Values and Neighborhood Harmony Since 1952
        
        #### NOTICE OF COVENANT VIOLATION
        
        Dear Mr. and Mrs. Walker,
        
        This letter serves as formal notification of several observed violations at your property located at 1137 Whispering Pine Lane. The Pinewood Estates HOA Board conducted its monthly inspection on June 4th and identified the following issues requiring immediate attention:
        
        1. Noise Disturbance (Section 5.2): We have received four separate complaints about low-frequency humming emanating from your property between 2:00 AM and 4:30 AM.
        2. Landscaping Concerns (Section 7.3): Your front yard contains non-approved plant species that have grown beyond the permitted height of 36 inches. The iridescent pollen from these specimens has been found on vehicles as far as Maple Street.
        3. Unauthorized Structure (Section 12.1): The decorative garden archway installed last month does not conform to approved designs.
        
        Please rectify these violations by June 20th. Failure to comply may result in fines of $75 per day per violation and/or loss of community pool privileges.
        
        The Board wishes to remind you that we selected Pinewood Estates specifically for its tranquil atmosphere, and we must all do our part to maintain the neighborhood's character.
        
        If you have any questions or wish to discuss these matters further, please attend our next HOA meeting in the community center. Please remember that all attendees must wear name tags and arrive no later than 7:05 PM.
        
        Sincerely,<br>
        Diana Chavez<br>
        Covenant Enforcement Committee Chair<br>
        Pinewood Estates HOA
        EOT,
        <<<EOT
        ## CENTERPOINT MEDICAL LABORATORIES
        
        ### Dermatological Assessment Report
        
        * Patient Name: Andrew Chen
        * Patient ID: 78221-B
        * Specimen Type: Skin biopsy (right forearm, 2cm lesion)
        * Collection Date: 04/21/1998
        * Report Date: 04/28/1998
        * Ordering Physician: Dr. Mei Lee
        * Test Requisition #: DERM-937462-04
        
        ### DERMATOLOGICAL ASSESSMENT RESULTS
        
        | Test | Result | Reference Result | Assessment |
        | ---- | ------ | ---------------- | ---------- |
        | Inflammatory Markers | Not Detected | Not Detected | CLEAR |
        | Fungal Infection | Not Detected | Not Detected | CLEAR |
        | Bacterial Culture | Not Detected | Not Detected | CLEAR |
        | Malignant Cells | Not Detected | Not Detected | CLEAR |
        | Weave Dissolution | Not Detected | Not Detected | CLEAR |
        | Non-Euclidean Cellular Deformation | **Detected** | Not Detected | **POSITIVE** |
        | Retrocausal Matter | Not Detected | Not Detected | CLEAR |
        
        #### Laboratory Comments:
        Standard dermatological assessments show no evidence of common skin conditions. However, the specimen tested positive for Non-Euclidean Cellular Deformation. Microscopic examination reveals tissue cells boundaries connecting at angles that sum to >360°, resulting in the visible purple discoloration of the skin.
        
        #### Physician Recommendations:
        * Apply specialized boundary cream (prescription attached)
        * Shield affected area from direct sunlight
        * Minimize travel to or from the Umbra until condition resolves
        * Document any changes in lesion appearance
        * Return for follow-up assessment in 14 days
        
        Laboratory Director: Dr. Michael Wong, MD, PhD
        
        _This report contains confidential patient information. Unauthorized disclosure of this information is strictly prohibited._
        EOT,
        <<<EOT
        Dear Melissa Gross,
        
        Thank you for your interest in the Research Assistant position at ⨑ⶾ⍶⩝⇹⽟⓲₥. We appreciate the time you took to share your background and experiences with us.
        
        After careful review of your qualifications, we regret to inform you that we will not be proceeding with your application at this time. While your credentials are impressive, our evaluation indicates several **compatibility concerns**, specifically:
        
        * Your aptitude test revealed pattern recognition that exceeds our safety thresholds
        * A graphoanalysis of your essay responses indicated recurring dreams of our facility despite no prior contact
        * Physics principles inconsistent with our branch of reality were detected in your saliva sample
        
        For the safety of both our current employees and yourself, we believe it would be inadvisable to proceed with your candidacy at this time.

        We wish you the best in your future endeavors.

        Sincerely,<br>
        ⪆⡓⟈↋⋥Ⓔ⤹⧗⚲☇<br>
        Director of Human Resources
        EOT,
        <<<EOT
        Dear Alex,

        I can't believe you've never heard of a PB&J! It's just bread with peanut butter and jelly in the middle. Most kids bring them for lunch. What do you usually eat at school? The cafeteria here serves pizza on Fridays which is the best day.

        Your science fair project sounds really cool! My teacher was surprised when I asked her about some of the things you were talking about. My volcano project seems pretty basic compared to yours.

        Mrs. Palmer is making us all do presentations next week. I'm nervous about speaking in front of everyone. Do you have to do presentations at your school too?

        Your friend,<br>
        Jamie

        P.S. I'm including a drawing of my lunch from yesterday. Maybe you can try making one sometime!
        EOT,
        <<<EOT
        LEGAL NOTICE OF SETTLEMENT<br>
        Meridian Group Consumer Study Participants<br>
        Case No. 02-CV-4721<br>
        
        Dear Trevor Fowler,
        
        This notice is to inform you that you are entitled to benefits from a class action settlement involving Meridian Group. Our records indicate that you participated in consumer research studies conducted by Meridian Group between May and September 2002.
        
        SETTLEMENT SUMMARY<br>
        As part of a court-approved settlement, Meridian Group has agreed to provide compensation to eligible participants. Enclosed is a check in the amount of $1,273.86 representing your portion of the settlement fund.
        
        CASE BACKGROUND<br>
        The lawsuit alleged certain procedural irregularities in Meridian Group's research protocols, specifically related to informed consent disclosures. While Meridian Group denies any wrongdoing, they have agreed to this settlement to resolve the matter efficiently.
        
        IMPORTANT INFORMATION<br>
        - You may not recall participating in these studies. This is not uncommon among participants and does not affect your eligibility.
        - The settlement includes a confidentiality provision regarding study methods and results.
        - Should you experience unexpected gap-like sensations when attempting to recall events from the study period, this is a documented side effect and typically resolves within 3-6 weeks.
        
        By cashing the enclosed check, you acknowledge acceptance of all settlement terms and release Meridian Group from any claims related to the referenced studies, including those you may become aware of at a later date.
        
        For questions about this settlement, please contact the administrator at 1-800-555-0199. Representatives are available Monday through Friday, 9am to 5pm.
        
        Sincerely,
        
        Martin Weiss<br>
        Settlement Administrator<br>
        Weiss & Bernstein LLP
        
        Note: Please review the enclosed privacy notice regarding Meridian Group's data retention policies for discontinued research initiatives.
        EOT,
    ];
}