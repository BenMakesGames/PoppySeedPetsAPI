<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/fourFunctionCalculator")]
class FourFunctionCalculatorController extends AbstractController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'fourFunctionCalculator/#/read');

        return $responseService->itemActionSuccess('
| Basic Operations |
| --- |

#### Batteries
The calculator uses 2 AAA (LR03) batteries as main power.

#### Power On/Off
To turn on the calculator, press the <kbd>PWR</kbd> key. To turn off the calculator, press and hold the <kbd>PWR</kbd> key.

#### Display
The calculator has a 10-digit display. If a result is longer than 10 digits, the calculator will display the result in scientific notation.

#### Clearing the Calculator
To clear the calculator, press the <kbd>AC</kbd> key.

| Suggested Applications |
| --- |

Your 4-function Calculator can assist with various computational tasks. Below are some common use cases and recommended procedures.

#### Viswanath\'s Constant
* Standard process possible without device
* Recommended sequence: [×] [÷] [×] [=]
* Hold equals key until display stabilizes

#### Strange Attractor
* Standard process possible without device
* Recommended sequence: [+] [×] [+] [=]
* Keep device 30cm from workspace

#### Hapax Legomenon
* Standard process possible without device
* Recommended sequence: [÷] [+] [÷] [=]
* Allow 3 seconds between calculations
* _Note: Device may produce audible feedback during processing_

| Regulatory Information |
| --- |

This product must not be disposed of with your other
household waste. Instead, it is your responsibility to dispose
of your waste equipment by handing it over to a designated
collection point for the recycling of waste electrical and
electronic equipment.

#### Federal Communications Commission (FCC) Compliance Notice
This equipment has been tested and found to comply with the limits for a Class B digital device, pursuant to Part 15 of the FCC Rules. These limits are designed to provide reasonable protection against harmful interference in a residential installation.

#### Canadian Notice
This Class B digital apparatus meets all requirements of the
Canadian Interference-Causing Equipment Regulations.

#### Avis Canadien
Cet appareil numérique de la classe B respecte toutes les
exigences du Règlement sur le matériel brouilleur du Canada.

#### Japanese Notice
こ の装置は、 情報処理装置等電波障害自主規制協議会 （VCCI） の基準に基づ くク ラ ス B
情報技術装置です。 こ の装置は、 家庭環境で使用する こ と を目的 と し ていますが、 こ の装
置がラ ジオやテ レ ビ ジ ョ ン受信機に近接 し て使用 さ れる と 、 受信障害を引き起 こ す こ と が
あ り ます。
取扱説明書に従 っ て正 し い取 り 扱い を し て くだ さ い。
');
    }
}
