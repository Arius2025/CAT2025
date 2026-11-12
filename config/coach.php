<?php

return [
    'cache_ttl_minutes' => env('COACH_CACHE_TTL', 15),
    'plan_days' => 7,
    'recent_window_days' => 14,
    'priority_weights' => [
        'low_accuracy' => 0.50,
        'recent_wrong_count' => 0.30,
        'topic_frequency' => 0.20,
        'staleness' => 0.15,
    ],
    'prediction' => [
        'readiness_accuracy_weight' => 0.55,
        'readiness_consistency_weight' => 0.15,
        'readiness_volume_weight' => 0.15,
        'readiness_trend_weight' => 0.15,
    ],
];
