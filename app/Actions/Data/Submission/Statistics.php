<?php

namespace App\Actions\Data\Submission;

use App\Enums\SubmissionStatusEnum;
use App\Models\Submission;


class Statistics
{
    /**
     * Get submission statistics based on type and status.
     *
     * @param string|null $type
     * @return array
     */
    public function data(?string $type = null): array
    {
        $cacheKey = 'submission_statistics_' . ($type ?? 'all');

        return cache()->remember($cacheKey, 300, function () use ($type) {
            $query = Submission::query();

            if ($type !== null) {
                $query->where('type', $type);
            }

            $counts = $query
                ->selectRaw('status, COUNT(id) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $statistics = [];
            foreach (SubmissionStatusEnum::cases() as $status) {
                $statistics[] = [
                    'status' => $status->value,
                    'label' => ucfirst(strtolower(str_replace('_', ' ', $status->name))),
                    'count' => $counts[$status->value] ?? 0,
                ];
            }

            return [
                'data' => $statistics,
            ];
        });
    }
}
