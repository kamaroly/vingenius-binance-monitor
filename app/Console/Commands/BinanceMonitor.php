<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class BinanceMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'binance:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor Binance markets';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        while(true){

            // Monitor
            $this->monitor();

            // Wait for 5 seconds
            usleep(5000);
        }
    }

    /**
     * Monitor Binance
     *
     * @return void
     */
    private function monitor(){
        // 1. Get binance data
        $binanceData = $this->getMarketData();

        // 2. Extract what is needed(USDT & USD) only
        $filteredData = $binanceData->filter(function($item){
                            return (
                                Str::endsWith($item["symbol"], 'USDT') || 
                                Str::endsWith($item["symbol"], 'USD')
                            );
                        })->map(function($item){

                            // Only pick what we need need
                            $previousPrice = Currency::bySymbol($item["symbol"])->lastPrice;
                            
                            // 3. Compare current extraction with previous extraction
                            $lastPriceVariance = $this->variance($item["lastPrice"], $previousPrice);

                            // 4. If it meets the threshold, then send alerts.
                            /** @todo Send alert */

                            // 5. Transform results for display
                            return [
                                "symbol" => $item["symbol"],
                                "priceChange" => number_format($item["priceChange"], 2),
                                "priceChangePercent" => number_format($item["priceChangePercent"], 2),
                                "weightedAvgPrice" => number_format($item["weightedAvgPrice"], 2),
                                "prevClosePrice" => number_format($item["prevClosePrice"], 2),
                                "lastPrice" => number_format($item["lastPrice"], 2),
                                "prevLastPrice" => $previousPrice,
                                "lastQty" => number_format($item["lastQty"], 2),
                                "lastPriceVariance" => number_format($lastPriceVariance, 3),
                            ];
                        })
                        ->sortByDesc(function ($currency, $key) {
                        return (float)  $currency["lastPriceVariance"];
                    })->take(10);
        
        // Store last Results in Table
        $this->storeResults($filteredData);
        

        // PREPARE DISPLAY ON THE SCREEN
        $this->info("BINANCE MONITORING RESULTS AT ". Carbon::now()->format("Y-m-d H:i:s"));
        $this->info("----------------------------------------------------");

        $this->renderTable($filteredData);
    }

    /**
     * Store results in DB
     *
     * @param Collection $results
     * @return void
     */
    public function storeResults(Collection $results){

        // Add time stamp before saving
        $resultsWithTime = $results->map(function($result){
            $result["created_at"] = Carbon::now();
            $result["updated_at"] = Carbon::now();
            return $result;
        });

        // Clean the table
        Currency::truncate();

        // Store last results
        return Currency::insert($resultsWithTime->toArray());
    }

    /**
     * Display results in a table
     *
     * @param [Collection] $data
     * @return void
     */
    public function renderTable(Collection $data){
        
        $tableHeaders = collect($data->first())->keys();
        // Display data on the screen
        $this->table(
           $tableHeaders->toArray(),
           $data->toArray()
        );
    }

    /**
     * Calculate variance
     *
     * @param float $currentPrice
     * @param float $previousPrice
     * @return float
     */
    public function variance(float $currentPrice, float $previousPrice): float{

        // Prevent divide by zero error
        if(intval($previousPrice) === 0 ){
            return 0;
        }

        return ($currentPrice / $previousPrice);
    }

    /**
     * Fetch latest market data
     *
     * @return collection
     */
    public function getMarketData()
    {
        return Http::get("https://api3.binance.com/api/v3/ticker/24hr")
        ->collect();
    }
}
