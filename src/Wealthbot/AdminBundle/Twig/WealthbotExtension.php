<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.12.12
 * Time: 13:45
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Twig;

class WealthbotExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'ucfirst' => new \Twig_Filter_Method($this, 'ucfirstFilter'),
            'ucwords' => new \Twig_Filter_Method($this, 'ucwordsFilter'),
        );
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

    function getName()
    {
        return 'rx_extension';
    }
}
