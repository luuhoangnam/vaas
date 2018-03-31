<?php

namespace App\Listeners;

use App\Events\FoundNewCompetitorItem;
use App\Jobs\ResearchCompetitorItemPerformance;
use App\Spy\CompetitorItem;
use Carbon\Carbon as PureCarbon;
use Carbon\Carbon;

class ResearchCompetitorItem
{
    public function handle(FoundNewCompetitorItem $event)
    {
        if ($this->willResearch($event->item)) {
            ResearchCompetitorItemPerformance::dispatch($event->item);
        }
    }

    protected function willResearch(CompetitorItem $item): bool
    {
        $qualifiedPrice      = $item['price'] > 10;
        $listedWithin365Days = Carbon::now()->diffInDays($item['start_time']) < 90;

        return $qualifiedPrice && $listedWithin365Days ? true : false;
    }

    protected function compareCarbon($field, $operator, $value)
    {
        $field = ($field instanceof PureCarbon ? $field : carbon($field))->startOfDay();
        $value = ($value instanceof PureCarbon ? $value : carbon($value))->startOfDay();

        if ($operator === '>' && $field->lessThan($value)) {
            return false;
        }

        if ($operator === '>=' && $field->greaterThan($value)) {
            return false;
        }

        if ($operator === '=' && $field->notEqualTo($value)) {
            return false;
        }

        if ($operator === '!=' && $field->equalTo($value)) {
            return false;
        }

        if ($operator === '<=' && $field->greaterThan($value)) {
            return false;
        }

        if ($operator === '<' && $field->greaterThanOrEqualTo($value)) {
            return false;
        }

        return true;
    }
}
