<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CustomsExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter("amount", [$this, "amount"]),
            new TwigFilter("sliceText", [$this, "sliceText"]),
        ];
    }
    
    /**
     * Convert an integer into amount of any money
     *
     * @param  int
     * @param  string
     * @param  string
     * @param  int
     * @param  string
     * @return string
     */
    public function amount(int $value, string $symbol = '€', string $decSep = ',', int $decimals = 2,  string $thousandSep = ' ')
    {
        $finalValue = $value / 100;

        $finalValue = number_format($finalValue, $decimals, $decSep, $thousandSep);
        
        return $finalValue." ".$symbol;
    }

    /**
     * A better slice filter
     *
     * @param  string $text
     * @param  int $min
     * @param  int $max
     * @param  string $end
     * @return string $finalText
     */
    public function sliceText(string $text, int $min = 0, int $max = 0, string $end = "[...]")
    {
        $finalText = substr($text, $min, $max);

        return $finalText.$end;
    }
}