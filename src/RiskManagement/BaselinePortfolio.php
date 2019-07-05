<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 14.09.12
 * Time: 17:29
 * To change this template use File | Settings | File Templates.
 */

namespace App\RiskManagement;

class BaselinePortfolio
{
    public static $models = ['100 Stocks', '90/10', '80/20', '70/30', '60/40', '50/50', '40/60', '30/70', '20/80', '10/90', '100 Bonds'];

    public static $modelPercentage = [
        '100_stocks' => ['stock' => 100, 'bond' => 0],
        '90_10' => ['stock' => 90, 'bond' => 10],
        '80_20' => ['stock' => 80, 'bond' => 20],
        '70_30' => ['stock' => 70, 'bond' => 30],
        '60_40' => ['stock' => 60, 'bond' => 40],
        '50_50' => ['stock' => 50, 'bond' => 50],
        '40_60' => ['stock' => 40, 'bond' => 60],
        '30_70' => ['stock' => 30, 'bond' => 70],
        '20_80' => ['stock' => 20, 'bond' => 80],
        '10_90' => ['stock' => 10, 'bond' => 90],
        '100_bonds' => ['stock' => 0, 'bond' => 100],
    ];

    public static function getInitialSuggestedPortfolio($value)
    {
        if ($value < 10) {
            $model = '20/80';
        } elseif ($value >= 10 && $value < 15) {
            $model = '30/70';
        } elseif ($value >= 15 && $value < 20) {
            $model = '60/40';
        } elseif ($value >= 20 && $value < 25) {
            $model = '70/30';
        } else {
            $model = '80/20';
        }

        $arr = array_flip(self::$models);

        return $arr[$model];
    }
}
