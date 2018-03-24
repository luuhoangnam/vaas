<?php

namespace App\Listeners;

use App\Events\FoundNewCompetitorItem;
use App\Jobs\ResearchCompetitorItemPerformance;
use App\Spy\CompetitorItem;
use Carbon\Carbon as PureCarbon;

class AutoResearchCompetitorItem
{
    public function handle(FoundNewCompetitorItem $event)
    {
        if ($this->willResearch($event->item)) {
            ResearchCompetitorItemPerformance::dispatch($event->item);
        }
    }

    protected function willResearch(CompetitorItem $item): bool
    {
        foreach (config('ebay.spying.auto_research') as $config) {
            $field = $item[$config['field']];

            if ($field === 'start_time' || $field === 'end_time') {
                return $this->compareCarbon($field, $config['operator'], $config['value']);
            }

            if ($config['operator'] === '>' && $field <= $config['value']) {
                return false;
            }

            if ($config['operator'] === '>=' && $field < $config['value']) {
                return false;
            }

            if ($config['operator'] === '=' && $field != $config['value']) {
                return false;
            }

            if ($config['operator'] === '!=' && $field == $config['value']) {
                return false;
            }

            if ($config['operator'] === '<=' && $field > $config['value']) {
                return false;
            }

            if ($config['operator'] === '<' && $field >= $config['value']) {
                return false;
            }
        }

        return true;
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
