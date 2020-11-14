<?php

namespace Phatnt99\AdvancedQuery\Traits;

trait HasCustomQuery
{
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    public function getCollection() {
        return $this->query->get();
    }
}