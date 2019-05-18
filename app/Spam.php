<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Spam extends Model
{
    public function detect($body)
    {
        $this->detectInvalidKeyWords($body);

        return false;
    }

    protected function detectInvalidKeyWords($body)
    {
        $invalidsKeyWords = [
            'yahoo Customer Support'
        ];

        foreach ($invalidsKeyWords as $keyword) {
            if (stripos($body, $keyword) !== false) {
                throw new \Exception('Your containes a spam');
            }
        }
    }
}
