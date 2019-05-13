<?php

if (!function_exists('download_from_s3')) {
    function download_from_s3($type, $filePath)
    {
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . basename($filePath));
        header("Content-Type: " . $type);

        return file_get_contents($filePath);
    }
}

if (!function_exists('gross_value')) {
    function gross_value($items, $qtyVar, $priceVar)
    {
        $price = 0;

        foreach ($items as $item) {
            $price += ($item[$qtyVar] * $item[$priceVar]);
        }

        return $price;
    }
}

if (!function_exists('after_discount')) {
    function after_discount($price, $discounts)
    {
        uasort($discounts, function ($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        foreach ($discounts as $d) {
            switch ($d['type']) {
                case 'DOLLAR':
                    $price -= $d['value'];
                    break;

                case 'PERCENTAGE':
                    $price -= ($price * $d['value'] / 100);
                    break;

                default:
                    break;
            }
        }

        return $price;
    }
}

if (!function_exists('count_units')) {
    function count_units($items, $qtyVar)
    {
        $count = 0;

        foreach ($items as $item) {
            $count += $item[$qtyVar];
        }

        return $count;
    }
}

if (!function_exists('state_code')) {
    function state_code($state)
    {
        $states = [
            ['code' => 'AU-NSW', 'state' => 'NEW SOUTH WALES'],
            ['code' => 'AU-QLD', 'state' => 'QUEENSLAND'],
            ['code' => 'AU-SA', 'state' => 'SOUTH AUSTRALIA'],
            ['code' => 'AU-TAS', 'state' => 'TASMANIA'],
            ['code' => 'AU-VIC', 'state' => 'VICTORIA'],
            ['code' => 'AU-WA', 'state' => 'WESTERN AUSTRALIA'],
        ];

        $key = array_search($state, array_column($states, 'state'));

        return $states[$key]['code'];
    }
}

if (!function_exists('response_headers')) {
    function response_headers($filename)
    {
        return [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];
    }
}
