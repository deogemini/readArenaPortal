<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class QuizAnswerSelection
{
    /** Validate each response and report automatic scores plus any responses needing review. */
    public static function evaluate(array $submitted, Collection $questions): array
    {
        $questionMap = $questions->keyBy('id');
        $errors = [];
        $provided = [];
        $results = [];
        $responses = [];

        foreach ($submitted as $questionId => $selection) {
            $normalizedQuestionId = filter_var($questionId, FILTER_VALIDATE_INT);
            $question = $normalizedQuestionId === false ? null : $questionMap->get((int) $normalizedQuestionId);

            if (! $question) {
                $errors['answers.'.$questionId] = 'This question does not belong to the quiz.';
                continue;
            }

            $id = (int) $normalizedQuestionId;
            $type = $question->question_type ?: 'multiple_choice';

            if (in_array($type, ['one_word', 'short_answer', 'written_response'], true)) {
                if (! is_string($selection)) {
                    $errors['answers.'.$questionId] = 'Enter a text response.';
                    continue;
                }

                $answer = trim($selection);
                $maxLength = match ($type) {
                    'one_word' => 100,
                    'short_answer' => 1000,
                    default => 5000,
                };

                if ($answer === '') {
                    $errors['answers.'.$questionId] = 'Enter a response for this question.';
                    continue;
                }

                if (mb_strlen($answer) > $maxLength) {
                    $errors['answers.'.$questionId] = 'The response is too long.';
                    continue;
                }

                if ($type === 'one_word' && preg_match('/\s/u', $answer)) {
                    $errors['answers.'.$questionId] = 'Enter one word only.';
                    continue;
                }

                $provided[$id] = true;
                $responses[$id] = [
                    'answer_text' => $answer,
                    'selected_answer_ids' => null,
                    'points_awarded' => null,
                ];

                if ($type === 'written_response') {
                    $results[$id] = null;
                    continue;
                }

                $acceptedAnswers = $question->answers
                    ->where('is_correct', true)
                    ->pluck('body')
                    ->map(fn ($value) => self::normalizeText((string) $value));
                $isCorrect = $acceptedAnswers->contains(self::normalizeText($answer));
                $results[$id] = $isCorrect;
                $responses[$id]['points_awarded'] = $isCorrect ? (int) $question->points : 0;

                continue;
            }

            $ids = is_array($selection) ? array_values($selection) : [$selection];

            if ($ids === []) {
                $errors['answers.'.$questionId] = 'Select at least one answer.';
                continue;
            }

            $normalizedIds = [];
            foreach ($ids as $answerId) {
                $normalizedId = filter_var($answerId, FILTER_VALIDATE_INT);
                if ($normalizedId === false || $normalizedId < 1) {
                    $errors['answers.'.$questionId] = 'Select valid answer choices.';
                    continue 2;
                }
                $normalizedIds[] = (int) $normalizedId;
            }

            if (count(array_unique($normalizedIds)) !== count($normalizedIds)) {
                $errors['answers.'.$questionId] = 'Do not select the same answer more than once.';
                continue;
            }

            if (in_array($type, ['single_choice', 'true_false'], true) && count($normalizedIds) !== 1) {
                $errors['answers.'.$questionId] = 'Select exactly one answer.';
                continue;
            }

            $allowedIds = $question->answers->pluck('id')->map(fn ($answerId) => (int) $answerId)->all();
            if (array_diff($normalizedIds, $allowedIds) !== []) {
                $errors['answers.'.$questionId] = 'Selected answers must belong to this question.';
                continue;
            }

            sort($normalizedIds);
            $provided[$id] = true;
            $correctIds = $question->answers
                ->where('is_correct', true)
                ->pluck('id')
                ->map(fn ($answerId) => (int) $answerId)
                ->sort()
                ->values()
                ->all();
            $isCorrect = $correctIds !== [] && $normalizedIds === $correctIds;

            $results[$id] = $isCorrect;
            $responses[$id] = [
                'answer_text' => null,
                'selected_answer_ids' => $normalizedIds,
                'points_awarded' => $isCorrect ? (int) $question->points : 0,
            ];
        }

        foreach ($questions as $question) {
            if (! isset($provided[(int) $question->id])) {
                $errors['answers.'.$question->id] ??= 'Answer every quiz question.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return [
            'correct_by_question' => $results,
            'responses' => $responses,
            'needs_review' => in_array(null, $results, true),
        ];
    }

    /** Backwards-compatible result helper used by existing single- and multi-choice callers. */
    public static function scoreMap(array $submitted, Collection $questions): array
    {
        return self::evaluate($submitted, $questions)['correct_by_question'];
    }

    private static function normalizeText(string $answer): string
    {
        $answer = mb_strtolower(trim($answer));
        $answer = preg_replace('/[\pP\pS]+/u', ' ', $answer) ?? $answer;
        $answer = preg_replace('/\s+/u', ' ', $answer) ?? $answer;

        return trim($answer);
    }
}
