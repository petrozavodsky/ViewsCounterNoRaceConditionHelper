<?php

namespace ViewsCounterNoRaceConditionHelper\Classes;


class Dispersion
{
    public static $randPercent = 20;

    public static $interval = 15;

    public function day()
    {
        $rand = function ($val) {
            $a = ['+', '-'];
            $type = $a[mt_rand(0, 1)];
            $percent = $type . mt_rand(1, self::$randPercent);

            return $val + $this->extractPercent($percent, $val);
        };

        $days = [
            0 => $this->hour(3000),
            1 => $this->hour(1100),
            2 => $this->hour($rand(1000)),
            3 => $this->hour($rand(600)),
            4 => $this->hour($rand(500)),
            5 => $this->hour($rand(950)),
            6 => $this->hour($rand(850)),
            8 => $this->hour($rand(650)),
            9 => $this->hour($rand(950)),
            10 => $this->hour($rand(400)),
            11 => $this->hour($rand(900)),
            12 => $this->hour($rand(200)),
            13 => $this->hour($rand(150)),

        ];

        return $days;
    }

    private function hour($total)
    {
        $index = 60 / intval(self::$interval);

        $detail = function ($val) use ($index) {
            $out = [];
            for ($i = 0; $i++ < $index;) {
                $out[$i * self::$interval] = $val / $index;
            }

            return $out;
        };

        $hours = [
            '00' => $detail (round($this->extractPercent(0.65, $total))),
            '01' => $detail (round($this->extractPercent(1.65, $total))),
            '03' => $detail (round($this->extractPercent(1.65, $total))),
            '04' => $detail (round($this->extractPercent(1.31, $total))),
            '06' => $detail (round($this->extractPercent(7.92, $total))),
            '07' => $detail (round($this->extractPercent(5.27, $total))),
            '08' => $detail (round($this->extractPercent(4.23, $total))),
            '09' => $detail (round($this->extractPercent(3.58, $total))),
            '10' => $detail (round($this->extractPercent(4.96, $total))),
            '11' => $detail (round($this->extractPercent(2.61, $total))),
            '12' => $detail (round($this->extractPercent(3.4, $total))),
            '13' => $detail (round($this->extractPercent(7.19, $total))),
            '14' => $detail (round($this->extractPercent(8.4, $total))),
            '15' => $detail (round($this->extractPercent(6.54, $total))),
            '16' => $detail (round($this->extractPercent(3.27, $total))),
            '17' => $detail (round($this->extractPercent(4.54, $total))),
            '18' => $detail (round($this->extractPercent(5.23, $total))),
            '19' => $detail (round($this->extractPercent(3.27, $total))),
            '20' => $detail (round($this->extractPercent(3.92, $total))),
            '21' => $detail (round($this->extractPercent(5.23, $total))),
            '22' => $detail (round($this->extractPercent(0.65, $total))),
            '23' => $detail (round($this->extractPercent(3.92, $total))),
        ];

        return $hours;
    }

    /**
     * Находит процент от числа
     *
     * @param $percent
     * @param $number
     * @return float|int
     */
    private function extractPercent($percent, $number)
    {
        $out = ($number / 100) * $percent;

        return $out;
    }


    public static function randHelper($number, $rand = 42)
    {
        $rand = mt_rand(1, $rand) / 100;

        if ((1 / 100) < $rand) {

            return $number + ($number * $rand);
        }

        return $number;
    }

}
