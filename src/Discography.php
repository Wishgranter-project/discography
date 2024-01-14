<?php
namespace AdinanCenci\Discography;

use AdinanCenci\Discography\Source\SourceInterface;
use AdinanCenci\Discography\Sorting\Comparer;
use AdinanCenci\Discography\Resource\Resource;

class Discography 
{
    /**
     * @var AdinanCenci\Discography\Source\SourceInterface[]
     */
    protected array $sources;

    /**
     * Add a source.
     *
     * @param AdinanCenci\Discography\Source\SourceInterface $source
     *   A service providing resources ( artists and releases ).
     * @param int $priority
     *   The priority, sources with higher priority will be consulted first.
     *
     * @return self
     */
    public function addSource(SourceInterface $source, int $priority) : Aether
    {
        $this->sources[] = [$source, $priority];
        if (count($this->sources) > 1) {
            $this->sortSources();
        }

        return $this;
    }

    /**
     * Sort sources based on their priority.
     */
    protected function sortSources() 
    {
        usort($this->sources, function($s1, $s2) 
        {
            if ($s1[1] == $s2[1]) {
                return 0;
            }

            return $s1[1] > $s2[1]
                ? -1
                :  1;
        });
    }
}