<?php

namespace App\Services;

use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuizQuestionDefinition
{
    /** Validate question-specific fields and return answer rows for QuizAnswerOptions::sync(). */
    public static function validate(
        mixed $type,
        mixed $options,
        mixed $correctIndices,
        mixed $trueFalseCorrect,
        mixed $acceptedAnswers,
        mixed $answerGuide,
    ): array {
        $type = Validator::make(['question_type' => $type], [
            'question_type' => ['required', 'string', Rule::in(QuizQuestion::TYPES)],
        ])->validate()['question_type'];

        if (in_array($type, ['single_choice', 'multiple_choice'], true)) {
            $payload = QuizAnswerOptions::validate($options, $correctIndices);

            if ($type === 'single_choice' && count($payload['correct_indices']) !== 1) {
                throw ValidationException::withMessages(['correct_options' => 'Single-choice questions must have exactly one correct answer.']);
            }

            return $payload;
        }

        if ($type === 'true_false') {
            $payload = Validator::make(['correct' => $trueFalseCorrect], [
                'correct' => ['required', 'integer', 'in:0,1'],
            ], ['correct.required' => 'Choose whether the statement is true or false.'])->validate();
            $correct = (int) $payload['correct'];

            return [
                'options' => [
                    ['body' => 'True'],
                    ['body' => 'False'],
                ],
                'correct_indices' => [$correct],
            ];
        }

        if (in_array($type, ['one_word', 'short_answer'], true)) {
            $maxLength = $type === 'one_word' ? 100 : 1000;
            $payload = Validator::make(['accepted_answers' => $acceptedAnswers], [
                'accepted_answers' => ['required', 'string', 'max:10000'],
            ], ['accepted_answers.required' => 'Enter at least one accepted answer, one per line.'])->validate();
            $lines = preg_split('/\r\n|\r|\n/', $payload['accepted_answers']);
            $answers = collect($lines)
                ->map(fn ($line) => trim($line))
                ->filter(fn ($line) => $line !== '')
                ->values();

            Validator::make(['answers' => $answers->all()], [
                'answers' => ['required', 'array', 'min:1', 'max:20'],
                'answers.*' => ['required', 'string', 'max:'.$maxLength, 'distinct:ignore_case'],
            ])->validate();

            if ($type === 'one_word' && $answers->contains(fn ($answer) => preg_match('/\s/u', $answer))) {
                throw ValidationException::withMessages(['accepted_answers' => 'One-word answers cannot contain spaces.']);
            }

            return [
                'options' => $answers->map(fn ($answer) => ['body' => $answer])->all(),
                'correct_indices' => array_keys($answers->all()),
            ];
        }

        $payload = Validator::make(['answer_guide' => $answerGuide], [
            'answer_guide' => ['required', 'string', 'max:5000'],
        ], ['answer_guide.required' => 'Provide a marking guide for written responses.'])->validate();
        $guide = trim($payload['answer_guide']);
        if ($guide === '') {
            throw ValidationException::withMessages(['answer_guide' => 'Provide a marking guide for written responses.']);
        }

        return [
            'options' => [['body' => $guide]],
            'correct_indices' => [0],
        ];
    }
}
