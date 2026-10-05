<label class="block">
    <span class="mb-1 block text-sm">Question format</span>
    <select name="{{ $typeField }}" class="w-full rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" data-question-type-select>
        @foreach([
            'single_choice' => 'Single choice',
            'multiple_choice' => 'Multiple correct choices',
            'true_false' => 'True or false',
            'one_word' => 'One-word answer',
            'short_answer' => 'Short answer',
            'written_response' => 'Written explanation (manual review)',
        ] as $type => $label)
            <option value="{{ $type }}" @selected($selectedType === $type)>{{ $label }}</option>
        @endforeach
    </select>
</label>

<div data-type-fields="choice">
    <p class="mb-3 text-xs text-[#d8c9ad]" data-choice-help>Enter four different choices and mark the correct answer.</p>
    @for($optionIndex = 0; $optionIndex < 4; $optionIndex++)
        <div class="mb-3 grid gap-2 rounded-xl border border-[#3d261b] bg-[#1B0D05] p-3 sm:grid-cols-[1fr_auto] sm:items-center">
            <input name="{{ $answerOptionsField }}[{{ $optionIndex }}][body]" value="{{ data_get($optionBodies, $optionIndex.'.body', data_get($optionBodies, $optionIndex, '')) }}" placeholder="Answer choice {{ $optionIndex + 1 }}" class="rounded-lg border border-[#3d261b] bg-[#1B0D05] px-4 py-2" data-choice-input>
            <label class="flex items-center gap-2 text-sm text-[#d8c9ad]"><input type="checkbox" name="{{ $correctOptionsField }}" value="{{ $optionIndex }}" @checked(in_array((string) $optionIndex, $selectedCorrectOptions, true)) data-correct-option> Correct answer</label>
        </div>
    @endfor
</div>

<div data-type-fields="true_false" hidden>
    <label class="block text-sm text-[#d8c9ad]">Correct answer
        <select name="{{ $trueFalseField }}" class="mt-2 w-full rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
            <option value="">Choose the correct value</option>
            <option value="0" @selected((string) $trueFalseCorrect === '0')>True</option>
            <option value="1" @selected((string) $trueFalseCorrect === '1')>False</option>
        </select>
    </label>
</div>

<div data-type-fields="accepted" hidden>
    <label class="block text-sm text-[#d8c9ad]">Accepted answer(s), one per line
        <textarea name="{{ $acceptedAnswersField }}" rows="4" placeholder="Enter every answer that should be accepted, one per line." class="mt-2 w-full rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">{{ $acceptedAnswersText }}</textarea>
    </label>
    <p class="mt-1 text-xs text-[#d8c9ad]">Text answers are checked automatically without regard to case or punctuation.</p>
</div>

<div data-type-fields="written_response" hidden>
    <label class="block text-sm text-[#d8c9ad]">Marking guide (visible to reviewers only)
        <textarea name="{{ $answerGuideField }}" rows="4" maxlength="5000" placeholder="Describe the key points an administrator should look for." class="mt-2 w-full rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">{{ $answerGuideText }}</textarea>
    </label>
    <p class="mt-1 text-xs text-[#d8c9ad]">Written explanations are sent to an administrator for manual scoring.</p>
</div>
