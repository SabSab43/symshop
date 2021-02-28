<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AmountExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter("amount", [$this, "amount"])
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
}