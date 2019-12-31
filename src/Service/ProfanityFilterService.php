<?php
namespace App\Service;

class ProfanityFilterService
{
    private const TRANSFORMS = [
        'f+u+c+k+' => 'love',
        'f+u+c+k+i+n+[g\']*' => 'lovin\'',
        'f+u+c+k+e+r+' => 'lover',
        'm+o+t+h+e+r+f+u+c+k+e+r+' => 'person',
        's+e+x+' => 'love',
        'c+u+n+t+' => 'muffin',
        'd+i+c+k+' => 'pickle',
        'c+o+c+k+' => 'pickle',
        'a+ss+?' => 'butt',
        'a+ss+h+o+l+e+?' => 'butt',
        'b+i+t+c+h+' => 'ne\'er-do-well',
        'b+a+s+t+a+r+d+' => 'ne\'er-do-well',
        'p+u+s+y+' => 'kitten',
        'r+a+p+e+' => 'harm',
        's+h+i+t+' => 'poo',
        's+h+i+t+y+' => 'poopy',
        'j+i+z+' => 'cream',
        'c+u+m+' => 'cream',
        'n+i+g+e+r+' => 'person',
        'm+i+l+f+' => 'person',
    ];

    public function __construct()
    {
    }

    public function filter(string $in): string
    {
        $out = $in;

        foreach(self::TRANSFORMS as $from=>$to)
        {
            $from = str_replace([ 's', 'o' ], [ '[s$]', '[o0]' ], $from);

            $out = preg_replace('/(^|[^a-z])' . $from . '([^a-z]|$)/i', '$1' . $to . '$2', $out);
        }

        return $out;
    }
}