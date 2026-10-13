<?php

return [
    'account_created_points' => (int) env('READ_ARENA_ACCOUNT_CREATED_POINTS', 50),
    'daily_login_points' => (int) env('READ_ARENA_DAILY_LOGIN_POINTS', 5),
    'quiz_completion_points' => (int) env('READ_ARENA_QUIZ_COMPLETION_POINTS', 10),
    'quiz_abandon_penalty_points' => (int) env('READ_ARENA_QUIZ_ABANDON_PENALTY_POINTS', 10),
    'starter_duel_limit' => (int) env('READ_ARENA_STARTER_DUEL_LIMIT', 3),
];
