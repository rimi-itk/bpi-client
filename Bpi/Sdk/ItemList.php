<?php
namespace Bpi\Sdk;

use Bpi\Sdk\Item\Facet;

/**
 * A generic item list.
 */
abstract class ItemList implements \Iterator, \Countable
{
    /**
     * @var \Bpi\Sdk\SimpleXMLElement|\SimpleXMLElement
     */
    protected $element;

    /**
     * Total amount of items on server
     *
     * @var int
     */
    public $total = 0;

    /**
     * @var int
     *
     * Position in $items array.
     */
    private $position = 0;

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var array
     */
    protected $facets = null;

    /**
     *
     * @param SimpleXMLElement $element
     */
    public function __construct(\SimpleXMLElement $element)
    {
        $this->element = $element;
        $this->total = $this->buildTotal();
        $this->items = $this->buildItems();
    }

    /**
     * Iterator interface implementation
     *
     * @group Iterator
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Returns same instance but with internal pointer to current item in collection
     *
     * @group Iterator
     */
    public function current()
    {
        return $this->items[$this->position];
    }

    /**
     * Key of current iteration position
     *
     * @group Iterator
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterate to next item
     *
     * @group Iterator
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Checks if is ready for iteration
     *
     * @group Iterator
     * @return boolean
     */
    public function valid()
    {
        return isset($this->items[$this->position]);
    }

    /**
     *
     * @return integer
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Get facets.
     *
     * @return Facets
     */
    public function getFacets()
    {
        if (!$this->facets) {
            $this->facets = $this->buildFacets();
        }

        return $this->facets;
    }

    /**
     * Build list of items.
     *
     * @return array
     *   An array of items.
     */
    abstract protected function buildItems();

    /**
     * Get total number of items.
     *
     * @return int
     *   The total number of items.
     */
    protected function buildTotal()
    {
        return (int)$this->element['total'];
    }

    /**
     * Build item facets.
     *
     * @return Facets
     */
    protected function buildFacets()
    {
        $facets = new Facets();
        foreach ($this->element->xpath('/*/facet') as $el) {
            $facet = new Facet();
            $facet->setFacetName((string)$el['name']);
            foreach ($el->xpath('term') as $term) {
                $facet->addFacetTerm((string)$term['name'], (string)$term['title'], (int)$term);
            }
            $facets->addFacet($facet);
        }

        return $facets;
    }
}
