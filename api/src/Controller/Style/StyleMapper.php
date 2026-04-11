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

namespace App\Controller\Style;

use App\Entity\UserStyle;

final class StyleMapper
{
    /**
     * @return array<string, mixed>|null
     */
    public static function mapMyStyle(?UserStyle $style): ?array
    {
        if($style === null)
            return null;

        return [
            ... self::mapPublicStyle($style),
            'name' => $style->getName()
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function mapPublicStyle(?UserStyle $style): ?array
    {
        if($style === null)
            return null;

        return [
            'id' => $style->getId(),
            'backgroundColor' => $style->getBackgroundColor(),
            'speechBubbleBackgroundColor' => $style->getSpeechBubbleBackgroundColor(),
            'textColor' => $style->getTextColor(),
            'primaryColor' => $style->getPrimaryColor(),
            'textOnPrimaryColor' => $style->getTextOnPrimaryColor(),
            'tabBarBackgroundColor' => $style->getTabBarBackgroundColor(),
            'linkAndButtonColor' => $style->getLinkAndButtonColor(),
            'buttonTextColor' => $style->getButtonTextColor(),
            'dialogLinkColor' => $style->getDialogLinkColor(),
            'warningColor' => $style->getWarningColor(),
            'gainColor' => $style->getGainColor(),
            'bonusAndSpiceColor' => $style->getBonusAndSpiceColor(),
            'bonusAndSpiceSelectedColor' => $style->getBonusAndSpiceSelectedColor(),
            'inputBackgroundColor' => $style->getInputBackgroundColor(),
            'inputTextColor' => $style->getInputTextColor(),
        ];
    }
}