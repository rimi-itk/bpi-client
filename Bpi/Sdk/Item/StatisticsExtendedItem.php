<?php

namespace Bpi\Sdk\Item;

/**
 * Class StatisticsExtendedItem.
 */
class StatisticsExtendedItem extends BaseItem {

    /**
     * Assembles a set of top items.
     *
     * @return array
     *   Set of top items.
     */
    public function getTop() {
        $top = $this->element->xpath('//bpi/item[@type="statistic" and @name="top"]');
        $properties = $top[0]->xpath('properties/property');

        $top = [];
        foreach ($properties as $property) {
            $item = [];
            foreach ($property->attributes() as $name => $value) {
                $item[$name] = (string)$value;
            }
            $item['value'] = (string)$property;
            $top[] = $item;
        }

        return $top;
    }
}
