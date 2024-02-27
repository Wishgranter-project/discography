<?php

namespace WishgranterProject\Discography\Source;

/**
 * Represents paged portion of search results.
 */
class SearchResults
{
    /**
     * @var array
     *   The actual results.
     */
    protected array $items;

    /**
     * @var int
     *   How many items are in $items.
     */
    protected int $count;

    /**
     * @var int
     *   The page $items can be find in.
     */
    protected int $page;

    /**
     * @var int
     *   How many pages of search results are there.
     */
    protected int $pages;

    /**
     * @var int
     *   The maximum number of items there should be in a page.
     */
    protected int $itensPerPage;

    /**
     * @var int
     *   How many results are there.
     */
    protected int $total;

    /**
     * @param array $items
     *   The actual results.
     * @param int $count
     *   How many items are in $items.
     * @param int $page
     *   The page $items can be find in.
     * @param int $pages
     *   How many pages of search results are there.
     * @param int $itensPerPage
     *   The maximum number of items there should be in a page.
     * @param int $total
     *   How many results are there.
     */
    public function __construct(
        array $items,
        int $count,
        int $page,
        int $pages,
        int $itensPerPage,
        int $total
    ) {
        $this->items        = $items;
        $this->count        = $count;
        $this->page         = $page;
        $this->pages        = $pages;
        $this->itensPerPage = $itensPerPage;
        $this->total        = $total;
    }

    public function __get($var)
    {
        return $this->{$var};
    }

    public static function empty(): SearchResults
    {
        return new self([], 0, 0, 0, 0, 0);
    }
}
