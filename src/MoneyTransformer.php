<?php

trait MoneyTransformer
{
    public function moneyMollie($price): string
    {

        return $this->moneyView(price: (int)round($price), separator: '.', decimalsReplacement: '00', threeDigitSeperator: '', currencySymbol: '', spaceAfterSymbol: false, symbolAtEnd: false);
    }

    public function percentageView($percentage): string
    {
        return $this->moneyView(price: (int)round($percentage), separator: ',', decimalsReplacement: '00', threeDigitSeperator: '', currencySymbol: '%', spaceAfterSymbol: true, symbolAtEnd: false);
    }

    /**
     * converts string (can include , or . as seperator) to an integer in cents format
     * @param string $price price in string (9,25 = 925, 9 = 900, 9.5 = 950)
     * @return int
     */
    public function moneyDB(string $price): int
    {
        if (preg_match('[\.|,]', $price)) {
            if (strlen(preg_split('[\.|,]', $price)[1]) === 1) {
                $newFormat = (int)str_replace([',', '.'], '', $price) * 10;
            } else {
                $newFormat = (int)str_replace([',', '.'], '', $price);
            }
        } else {
            $newFormat = (int)$price * 100;
        }

        return $newFormat;
    }

    /**
     * converts integer as cents to EUR format using ISO 4217 standards
     * @param integer $price price in integer (925 = 9.25 EUR)
     * @return string
     */
    public function moneyFB($price): string
    {
        return $this->moneyIso4217((int)round($price));
    }

    /**
     * converts integer as cents to EUR format using ISO 4217 standards
     * @param integer $price price in integer (925 = 9.25 EUR)
     * @return string
     */
    public function moneyGoogle($price): string
    {
        return $this->moneyIso4217((int)round($price));
    }

    public function moneyIso4217($price): string
    {
        $priceStr = strval((int)round($price));
        $arr = str_split($priceStr);

        if (count($arr) < 3) {
            $priceStr = strlen($priceStr) == 2 ? "0$priceStr" : "00$priceStr";
            $arr = str_split($priceStr);
        }

        array_splice($arr, count($arr) - 2, 0, '.');
        return implode('', $arr).' EUR';
    }

    /**
     * formats integer as cents to (??? 9,25, ??? 9,-) using ISO 4217 standards
     * @param int $price price in integer (925 = ??? 9,25)
     * @param string|null $separator use , . or something else to divide the cents
     * @param string|null $decimalsReplacement replace double null (00) decimals to some custom string like - (5,-)
     * @param string|null $threeDigitSeperator seperates every 3 digits with a character e.g. whitespace (1 000 000)
     * @param string|null $currencySymbol
     * @param bool|null $spaceAfterSymbol set true to set whitespace between symbol and price
     * @param bool|null $symbolAtEnd
     * @return string
     */
    public function moneyView($price, string $separator = null, string $decimalsReplacement = null, string $threeDigitSeperator = null, string $currencySymbol = null, bool $spaceAfterSymbol = null, bool $symbolAtEnd = null): string
    {
        $separator = $separator !== null ? $separator : ',';
        $decimalsReplacement = $decimalsReplacement !== null ? $decimalsReplacement : '00';
        $threeDigitSeperator = $threeDigitSeperator !== null ? $threeDigitSeperator : '.';
        $currencySymbol = $currencySymbol !== null ? $currencySymbol : '???';
        $spaceAfterSymbol = $spaceAfterSymbol !== null ? $spaceAfterSymbol : true;
        $symbolAtEnd = $symbolAtEnd !== null ? $symbolAtEnd : false;

        $positive = true;
        if ($price < 0) {
            $price *= -1;
            $positive = false;
        }

        $priceStr = strval((int)round($price));
        $arr = str_split($priceStr);

        if (count($arr) < 3) {
            $priceStr = strlen($priceStr) == 2 ? "0$priceStr" : "00$priceStr";
            $arr = str_split($priceStr);
        }

        array_splice($arr, count($arr) - 2, 0, $separator);

        $count = count($arr);
        if ($arr[$count -2] == 0 && $arr[$count -1] == 0) {
            array_pop($arr);
            array_pop($arr);
            $arr[] = $decimalsReplacement;
        }

        $implodeShit = implode('', $arr);
        $splitComma = explode($separator, $implodeShit);

        $modulo = strlen($splitComma[0]) % 3;
        $chunk1 = implode('', array_slice(str_split($splitComma[0]), 0, $modulo));
        $chunks = array_chunk(array_slice(str_split($splitComma[0]), $modulo), 3);
        $chunk2 = [];
        foreach ($chunks as $chunk) {
            $chunk2[] = implode('', $chunk);
        }

        $threeDigtSeperator = !$chunk2 ? '' : $threeDigitSeperator;

        return ($symbolAtEnd ? '' : $currencySymbol) . ($spaceAfterSymbol ? ' ' : '') . ($positive ? '' : '-') . $chunk1 . ($chunk1 === '' ? '' : $threeDigtSeperator) . implode($threeDigitSeperator, $chunk2) . (!empty($splitComma[1]) ? $separator : $splitComma[1]) . $splitComma[1] . ($symbolAtEnd && $spaceAfterSymbol ? ' ' : '') . ($symbolAtEnd ? $currencySymbol : '');
    }
}
