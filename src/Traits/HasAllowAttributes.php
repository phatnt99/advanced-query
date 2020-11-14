<?php

namespace Phatnt99\AdvancedQuery\Traits;

trait HasAllowAttributes
{
    public function setAllowAttrs($allows)
    {
        $this->allows = $allows;

        return $this;
    }
}