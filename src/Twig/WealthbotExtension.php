<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.12.12
 * Time: 13:45
 * To change this template use File | Settings | File Templates.
 */

namespace App\Twig;

class WealthbotExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            'ucfirst' => new \Twig_SimpleFilter('ucfirst', [$this, 'ucfirstFilter']),
            'ucwords' => new \Twig_SimpleFilter('ucwords', [$this, 'ucwordsFilter']),
        ];
    }

    public function ucfirstFilter($str)
    {
        if (!is_string($str)) {
            return $str;
        }

        return ucfirst($str);
    }

    public function ucwordsFilter($str)
    {
        if (!is_string($str)) {
            return $str;
        }

        return ucwords($str);
    }

    public function getName()
    {
        return 'rx_extension';
    }
}
