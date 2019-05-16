<?php

namespace App\Services;

use Rs\JsonLines\JsonLines;
use Carbon\Carbon;

class ProcessingService
{
    /**
     * Calculating the order's gross price
     *
     * @param  array  $items        array of ordered item type
     * @param  string $qtyVar       name of the quantity variable
     * @param  string $priceVar     name of the price variable
     * @return float  $price
     */
    private function grossPrice($items, $qtyVar, $priceVar)
    {
        $price = 0;

        foreach ($items as $item) {
            $price += ($item[$qtyVar] * $item[$priceVar]);
        }

        return $price;
    }

    /**
     * Calculating the order's price after discount
     *
     * @param  float  $price        order's gross price
     * @param  array  $discounts    array of available discounts
     * @return float  $price
     */
    private function afterDiscount($price, $discounts)
    {
        // Sorting discounts by priority
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

    /**
     * Counting the total ordered units
     *
     * @param  array  $items        array of ordered item type
     * @param  string $qtyVar       name of the quantity variable
     * @return int    $count
     */
    private function countUnits($items, $qtyVar)
    {
        $count = 0;

        foreach ($items as $item) {
            $count += $item[$qtyVar];
        }

        return $count;
    }

    /**
     * Formatting the result
     *
     * @param  array  $data         single order data
     * @param  float  $price        total price of single order
     * @return array
     */
    private function formatResult($data, $price)
    {
        $totalUnits = $this->countUnits($data['items'], 'quantity');

        return [
            'order_id' => $data['order_id'],
            'order_datetime' => Carbon::parse($data['order_date'])->toIso8601String(),
            'total_order_value' => number_format((float) $price, 2, '.', ''),
            'average_unit_price' => number_format((float) $price / $totalUnits, 2, '.', ''),
            'distinct_unit_count' => count($data['items']),
            'total_units_count' => $totalUnits,
            'customer_state' => $this->stateCode(ucwords($data['customer']['shipping_address']['state']))
        ];
    }

    /**
     * Getting the state code from inputted state
     *
     * @param  string  $state
     * @return string
     */
    public function stateCode($state)
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

    /**
     * Converting jsonl data into array
     *
     * @param  string  $object
     * @return array
     */
    public function jsonlToArray($object)
    {
        $json = (new JsonLines())->deline($object);
        return json_decode($json, true);
    }

    /**
     * Processing the order data
     *
     * @param  string  $input
     * @return array
     */
    public function processData($input)
    {
        $datas = $this->jsonlToArray($input);
        $results = [];

        foreach ($datas as $d) {
            $grossPrice = $this->grossPrice($d['items'], 'quantity', 'unit_price');
            $price = $this->afterDiscount($grossPrice, $d['discounts']);

            if ($price == 0) {
                continue;
            }

            $results[] = $this->formatResult($d, $price);
        }

        return $results;
    }
}