<?php

namespace App\Inspections;

use Exception;

class InvalidKeyWords
{
    protected $keyWords = [
        'yahoo Customer Support'
    ];

    public function detect($body)
    {
        foreach ($this->keyWords as $keyword) {
            if (stripos($body, $keyword) !== false) {
                throw new Exception('Your containes a spam');
            }
        }
    }
}
