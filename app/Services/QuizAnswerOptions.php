<?php

namespace App\Services;

use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Validator;

class QuizAnswerOptions
{
    /**
     * Validate four answer choices and at least one selected correct choice.
     * Correct choice indexes are zero-based to match the form fields.
     */
    public static function validate(mixed $options, mixed $correctIndices, int $maxLength = 1000): array
    {
        $validator = Validator::make(
            ['options' => $options, 'correct_indices' => $correctIndices],
            [
                'options' => ['required', 'array', 'size:4'],
                'options.*.body' => ['required', 'string', 'max:'.$maxLength, 'distinct'],
                'correct_indices' => ['required', 'array', 'min:1', 'max:4'],
                'correct_indices.*' => ['required', 'integer', 'between:0,3', 'distinct'],
            ],
            [
                'options.size' => 'Provide exactly four answer choices.',
                'options.*.body.distinct' => 'Each answer choice must be different.',
                'correct_indices.required' => 'Mark at least one answer choice as correct.',
                'correct_indices.min' => 'Mark at least one answer choice as correct.',
            ],
        );

        $validator->after(function ($validator) use ($options): void {
            if (! is_array($options)) {
                return;
            }

            $keys = array_keys($options);
            sort($keys);

            if ($keys !== [0, 1, 2, 3]) {
                $validator->errors()->add('options', 'Provide exactly four answer choices.');
            }
        });

        $payload = $validator->validate();
        ksort($payload['options']);
        $payload['correct_indices'] = array_map('intval', $payload['correct_indices']);

        return $payload;
    }

    /** Update or create the four answer rows in displayed order. */
    public static function sync(QuizQuestion $question, array $payload): void
    {
        $existing = $question->answers()->orderBy('id')->get();

        foreach (range(0, 3) as $index) {
            $answerData = [
                'body' => $payload['options'][$index]['body'],
                'is_correct' => in_array($index, $payload['correct_indices'], true),
            ];
            $answer = $existing->get($index);

            if ($answer instanceof QuizAnswer) {
                $answer->update($answerData);
            } else {
                $question->answers()->create($answerData);
            }
        }

        $existing->slice(4)->each(fn (QuizAnswer $answer) => $answer->delete());
    }
}
