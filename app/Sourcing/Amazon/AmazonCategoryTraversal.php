<?php

namespace App\Sourcing\Amazon;

use Illuminate\Support\Collection;

class AmazonCategoryTraversal
{
    /**
     * @var Collection
     */
    protected $categories = null;

    protected $topLevel = null;

    protected $rootBrowseNode;

    public function __construct(array $rootBrowseNode)
    {
        $this->rootBrowseNode = $rootBrowseNode;

        $this->categories = new Collection;

        $this->rescursiveWalkThroughCategories($this->rootBrowseNode);
    }

    public function topLevelCategory(): array
    {
        return $this->categories->last();
    }

    public function getFlatCategories()
    {
        return $this->categories;
    }

    public function isCross(array $outsiderIds): bool
    {
        foreach ($outsiderIds as $outsiderId) {
            if ($this->isIn($outsiderId)) {
                return true;
            }
        }

        return false;
    }

    public static function isInBrowseNodes(array $rootBrowseNode, $categoryID): bool
    {
        return (new static($rootBrowseNode))->isIn($categoryID);
    }

    public function isIn($categoryID): bool
    {
        foreach ($this->categories as $category) {
            if ($category['id'] == $categoryID) {
                return true;
            }
        }

        return false;
    }

    protected function rescursiveWalkThroughCategories($browseNode): array
    {
        foreach ($browseNode as $key => $value) {
            if (is_int($key)) {
                $this->categories->push($this->normalizeCategory($value));

                if (@$value['Ancestors']) {
                    return $this->rescursiveWalkThroughCategories($value['Ancestors']['BrowseNode']);
                }
            } else {
                $this->categories->push($this->normalizeCategory($browseNode));

                break; // This is not a multi categories node. Save the loop
            }
        }

        if (@$browseNode['Ancestors']) {
            return $this->rescursiveWalkThroughCategories($browseNode['Ancestors']['BrowseNode']);
        }

        return $browseNode;
    }

    private function normalizeCategory($browseNode): array
    {
        return [
            'id'   => (int)$browseNode['BrowseNodeId'],
            'name' => $browseNode['Name'],
        ];
    }
}