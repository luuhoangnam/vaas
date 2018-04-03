<?php

namespace App\Console\Commands;

use App\eBay\TradingAPI;
use App\Miner\Competitor;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\GetUserRequestType;
use Illuminate\Console\Command;

class AddCompetitor extends Command
{
    protected $signature = 'competitor:add {username}';

    protected $description = 'Add Competitor to Miner';

    public function handle()
    {
        $username = $this->argument('username');

        // 1. Validate Competitor by using eBay API Request
        $request = new GetUserRequestType([
            'UserID' => (string)$username,
        ]);

        $response = TradingAPI::random()->getUser($request);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            $this->error('Invalid username');

            return;
        }

        // 2. Add to database if valid
        $userType = $response->User;

        Competitor::query()->updateOrCreate(compact('username'), [
            'username'                  => $userType->UserID,
            'feedback_score'            => $userType->FeedbackScore,
            'positive_feedback_percent' => $userType->PositiveFeedbackPercent,
            'country'                   => $userType->Site,
        ]);

        $this->info('Success!');
    }
}
