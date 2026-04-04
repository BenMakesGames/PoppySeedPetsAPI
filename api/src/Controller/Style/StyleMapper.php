<?php
declare(strict_types=1);

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
            'tabBarBackground' => $style->getTabBarBackgroundColor(),
            'linkAndButtonColor' => $style->getLinkAndButtonColor(),
            'buttonTextColor' => $style->getButtonTextColor(),
            'dialogLinkColor' => $style->getDialogLinkColor(),
            'warningColor' => $style->getWarningColor(),
            'gainColor' => $style->getGainColor(),
            'bonusAndSpiceColor' => $style->getBonusAndSpiceColor(),
            'bonusAndSpiceCollectedColor' => $style->getBonusAndSpiceSelectedColor(),
            'inputBackgroundColor' => $style->getInputBackgroundColor(),
            'inputTextColor' => $style->getInputTextColor(),
        ];
    }
}