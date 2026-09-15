<?php

namespace App\Policies;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;

class ReadingPlanPolicy
{
    /**
     * Determine whether the user can update the reading plan.
     */
    public function update(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id;
    }

    /**
     * Determine whether the user can delete the reading plan.
     */
    public function delete(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id;
    }

    /**
     * Determine whether the user can mark the reading plan as completed.
     */
    public function complete(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id
            && $plan->status !== ReadingPlanStatus::Completed;
    }
}
