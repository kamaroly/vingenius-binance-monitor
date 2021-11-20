<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable=[
        "symbol",
        "priceChange",
        "priceChangePercent",
        "weightedAvgPrice",
        "prevClosePrice",
        "lastPrice",
        "lastQty",
        "prevLastPrice",
        "lastPriceVariance",
        "created_at",
        "updated_at"
    ];

    /**
     * Get Currency by symbol
     *
     * @param string $symbol
     * @return self
     */
    public function bySymbol(string $symbol): self{
        $currency = self::where("symbol", $symbol)->first();
        return $currency ? $currency: new self;
    }


    /**
     * Get Last Price Column
     *
     * @return float
     */
    public function getLastPriceAttribute(): float{
        
        return (empty($this->attributes["lastPrice"]) ? 
                0 : 
                (float) $this->attributes["lastPrice"]);
    }
}
