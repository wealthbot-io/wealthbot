<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 22.08.13
 * Time: 15:05
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

use Doctrine\Common\DataFixtures\AbstractFixture;

abstract class AbstractCsvFixture extends AbstractFixture
{
    protected $csvDir = '/../DataFixtures/CSV';

    protected function getCsvData($filename, $maxLength = 1000, $delimiter = ';', $isAbsolute = false)
    {
        if ($isAbsolute) {
            $path = $filename;
        } else {
            $path = getcwd().'/src/DataFixtures/CSV/'.$filename;
        }

        $handle = fopen($path, 'r');
        $data = [];

        if (false !== $handle) {
            while ($item = fgetcsv($handle, $maxLength, $delimiter)) {
                $data[] = $item;
            }

            fclose($handle);
        }

        return $data;
    }
}
