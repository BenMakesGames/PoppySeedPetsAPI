<?php
declare(strict_types=1);

namespace App\Functions;

// logic taken from https://github.com/Kaivosukeltaja/php-indefinite-article/blob/master/IndefiniteArticle.class.php
// (which itself took logic from https://metacpan.org/pod/distribution/Lingua-EN-Inflect/lib/Lingua/EN/Inflect.pm)
final class GrammarFunctions
{
    // THIS PATTERN MATCHES STRINGS OF CAPITALS STARTING WITH A "VOWEL-SOUND"
    // CONSONANT FOLLOWED BY ANOTHER CONSONANT, AND WHICH ARE NOT LIKELY
    // TO BE REAL WORDS (OH, ALL RIGHT THEN, IT'S JUST MAGIC!)

    private const A_abbrev = "(?! FJO | [HLMNS]Y.  | RY[EO] | SQU
          | ( F[LR]? | [HL] | MN? | N | RH? | S[CHKLMNPTVW]? | X(YL)?) [AEIOU])
            [FHLMNRSX][A-Z]
        ";

    // THIS PATTERN CODES THE BEGINNINGS OF ALL ENGLISH WORDS BEGINING WITH A
    // 'y' FOLLOWED BY A CONSONANT. ANY OTHER Y-CONSONANT PREFIX THEREFORE
    // IMPLIES AN ABBREVIATION.

    private const A_y_cons = 'y(b[lor]|cl[ea]|fere|gg|p[ios]|rou|tt)';

    // EXCEPTIONS TO EXCEPTIONS

    private const A_explicit_an = "euler|hour(?!i)|heir|honest|hono";

    private const A_ordinal_an = "[aefhilmnorsx]-?th";

    private const A_ordinal_a = "[bcdgjkpqtuvwyz]-?th";

    public static function indefiniteArticle($word)
    {
        // HANDLE ORDINAL FORMS
        if(preg_match("/^(".self::A_ordinal_a.")/i", $word))    return 'a';
        if(preg_match("/^(".self::A_ordinal_an.")/i", $word))   return 'an';

        // HANDLE SPECIAL CASES
        if(preg_match("/^(".self::A_explicit_an.")/i", $word))  return 'an';
        if(preg_match("/^[aefhilmnorsx]$/i", $word))            return 'an';
        if(preg_match("/^[bcdgjkpqtuvwyz]$/i", $word))          return 'a';

        // HANDLE ABBREVIATIONS
        if(preg_match("/^(".self::A_abbrev.")/x", $word))       return 'an';
        if(preg_match("/^[aefhilmnorsx][.-]/i", $word))         return 'an';
        if(preg_match("/^[a-z][.-]/i", $word))                  return 'a';

        // HANDLE CONSONANTS
        if(preg_match("/^[^aeiouy]/i", $word))                  return 'a';

        // HANDLE SPECIAL VOWEL-FORMS
        if(preg_match("/^e[uw]/i", $word))                      return 'a';
        if(preg_match("/^onc?e\b/i", $word))                    return 'a';
        if(preg_match("/^uni([^nmd]|mo)/i", $word))             return 'a';
        if(preg_match("/^ut[th]/i", $word))                     return 'an';
        if(preg_match("/^u[bcfhjkqrst][aeiou]/i", $word))       return 'a';

        // HANDLE SPECIAL CAPITALS
        if(preg_match("/^U[NK][AIEO]?/", $word))                return 'a';

        // HANDLE VOWELS
        if(preg_match("/^[aeiou]/i", $word))                    return 'an';

        // HANDLE y... (BEFORE CERTAIN CONSONANTS IMPLIES (UNNATURALIZED) "i.." SOUND)
        if(preg_match("/^(".self::A_y_cons.")/i", $word))       return 'an';

        // OTHERWISE, GUESS "a"
        return 'a';
    }

    // from https://stackoverflow.com/questions/3109978/display-numbers-with-ordinal-suffix-in-php
    public static function ordinal(int $number): string
    {
        $ends = [ 'th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th' ];

        if((($number % 100) >= 11) && (($number%100) <= 13))
            return 'th';
        else
            return $ends[$number % 10];
    }

    public static function ordinalize(int $number): string
    {
        return $number . self::ordinal($number);
    }

    public static function stretchWord(string $word, int $stretchLength = 3): string
    {
        $newWord = '';
        $wordLength = mb_strlen($word);
        $wordLower = mb_strtolower($word);

        $isVowel = fn($letter) => $letter == 'a' || $letter == 'e' || $letter == 'i' || $letter == 'o' || $letter == 'u' || $letter == 'y';

        for($i = 0; $i < $wordLength; $i++)
        {
            $newWord .= substr($word, $i, 1);

            if(
                $isVowel(mb_substr($wordLower, $i, 1)) &&
                (
                    $i == $wordLength - 1 ||
                    !$isVowel(mb_substr($wordLower, $i + 1, 1))
                )
            )
            {
                $newWord .= str_repeat(mb_substr($wordLower, $i, 1), $stretchLength - 1);
            }
        }

        return $newWord;
    }
}
