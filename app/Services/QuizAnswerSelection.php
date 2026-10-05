<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class QuizAnswerSelection
{
    /**
     * Validate that each quiz question has only its own choices selected.
     * A scalar answer ID remains accepted for older mobile clients.
     * Returns question ID => whether the complete correct choice set was selected.
     */
    public static function scoreMap(array $submitted, Collection $questions): array
    {
        $questionMap = $questions->keyBy('id');
        $errors = [];
        $selectedByQuestion = [];

        foreach ($submitted as $questionId => $selection) {
            $normalizedQuestionId = filter_var($questionId, FILTER_VALIDATE_INT);
            $question = $normalizedQuestionId === false ? null : $questionMap->get((int) $normalizedQuestionId);

            if (! $question) {
                $errors['answers.'.$questionId] = 'This question does not belong to the quiz.';
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

            $allowedIds = $question->answers->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (array_diff($normalizedIds, $allowedIds) !== []) {
                $errors['answers.'.$questionId] = 'Selected answers must belong to this question.';
                continue;
            }

            sort($normalizedIds);
            $selectedByQuestion[(int) $normalizedQuestionId] = $normalizedIds;
        }

        foreach ($questions as $question) {
            if (! array_key_exists((int) $question->id, $selectedByQuestion)) {
                $errors['answers.'.$question->id] ??= 'Answer every quiz question.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $scores = [];
        foreach ($questions as $question) {
            $correctIds = $question->answers
                ->where('is_correct', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values()
                ->all();

            $scores[(int) $question->id] = $correctIds !== []
                && $selectedByQuestion[(int) $question->id] === $correctIds;
        }

        return $scores;
    }
}
