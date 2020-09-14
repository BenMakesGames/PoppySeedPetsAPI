<?php
namespace App\Service;

class ProfanityFilterService
{
    private const TRANSFORMS = [
        's+e+x+($|[^a-z])' => 'love$1',
        's+e+x+y+' => 'lovely',
        'c+u+n+t+' => 'muffin',
        'b+o+n+e+r+(s*)' => 'pickle$1',
        'd+i+c+k+(s*)($|[^a-z])' => 'pickle$1$2',
        '($|[^a-z])c+o+c+k+(s*)($|[^a-z])' => '$1pickle$2$3',
        'f+a+t+a+s+' => 'butt',
        '(^|[^a-z])a+ss+([^a-z]|$)' => '$1butt$2',
        '(a+s+|b+u+t+)h+o+l+e+(s*)' => 'butt$2',
        '(^|[^a-z])t+i+t+(i+e+|y+)?s+($|[^a-z])' => '$1muffins$3',
        '(^|[^a-z])t+i+t+y*($|[^a-z])' => '$1muffin$2',
        'b+i+t+c+h+e+s+' => 'ne\'er-do-wells',
        'b+i+t+c+h+' => 'ne\'er-do-well',
        'b+a+s+t+a+r+d+(s*)' => 'ne\'er-do-well$1',
        'p+u+s+(y+|i+e+)' => 'kitten',
        'c+l+i+t+($|[^io])' => 'gem$1',
        '(^|[^cdg]|the)r+a+p+e+(s*)($|[^a-z])' => '$1wrong$2$3',
        '(^|s)c+r+a+p+y+' => '$1poopy',
        '(^|s)c+r+a+p+(s*)' => '$1poo$2',
        's+h+i+t+y+' => 'poopy',
        's+h+i+t+($|[^a])' => 'poo$1',
        'j+i+z+m*' => 'goo',
        '(^|[^a-z])c+u+m+(s*)($|[^a-z])' => '$1goo$1$2',
        '(^|[^s])c+u+m+i+n+g+($|[^s])' => '$1having fun$2',
        '(n+i+gg+e+r+|m+i+l+f+|m+o+t+h+e+r+[^a-z]*f+u+c+k+e+r+|f+a+g+|f+a+g+o+t+|r+e+t+a+r+d+|w+h+o+r+e+)s+' => 'people',
        '(n+i+gg+e+r+|m+i+l+f+|m+o+t+h+e+r+[^a-z]*f+u+c+k+e+r+|f+a+g+|f+a+g+o+t+|r+e+t+a+r+d+|w+h+o+r+e+)' => 'person',
        '(s+c+r+e+w+|b+l+o+w+|k+i+l+l+)[^a-z]*y+o+u+' => 'love you',
        '(h+a+n+d+|b+l+o+w+)[^a-z]*j+o+b+' => 'favor',
        '((j+a+c+k+|j+i+l+l+)[^a-z]*o+ff+|m+a+s+t+u+r+b+a+t+e+)' => 'have fun',
        'f+u+c+k+i+n+[g\']*' => 'pleasing',
        'f+u+c+k+e+r+' => 'pleaser',
        'f+u+c+k+' => 'please',
    ];

    public function __construct()
    {
    }

    public function filter(string $in): string
    {
        $out = $in;

        foreach(self::TRANSFORMS as $from=>$to)
        {
            $from = str_replace(
                [ 'a+', 'b+', 'c+', 'd+', 'e+', 'f+', 'i+', 'l+', 'n+', 'o+', 'r+', 's+', 's*', 't+', 'u+', 'x+', 'y+', 'z+' ],
                [ '[aªÀÁÂÃÄÅàáâãäå@]+', '[bßþ]+', '[ck¢©Çç]+', '[dÐð]+', '[e€ÈÉÊËèéêë3]+', '[fƒ£]+', '[i1¡ÌÍÎÏìíîï|]+', '[l£|]+', '[nÑñ]+', '[o0¤°ºÒÓÔÕÖØòóôõöø]+', '[r®]+', '[s$5§]+', '[s$5Šš]*', '[t†]+', '[uµÙÚÛÜùúûü]+', '[x×]+', '[yŸ¥Ýýÿ]+', '[zŽž]+' ],
                $from
            );

            $out = preg_replace('/' . $from . '/i', $to, $out);
        }

        return $out;
    }
}
