<tr class="student-row">
    <td class="frozen-column student-name">
        <div class="name-cell">
            @if($student->photo)
                <img src="{{ route('filament.app.student.photo', $student->id) }}"
                    alt="{{ $student->full_name }}"
                    class="avatar-image"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            @endif
            <div class="avatar-initials" @if($student->photo) style="display: none;" @endif>
                {{ $student->initials }}
            </div>
            <span title="{{ $student->complete_name }}">{{ $student->full_name }}</span>
        </div>
    </td>

    @foreach($gradeService->assessmentsByComponent() as $gradingComponentId => $assessments)
        @foreach($assessments as $assessment)
            @php $score = $assessment->getScore($student->id); @endphp
            <td class="score-cell" title="{{ $assessment->name }} Raw Score">
                <span class="{{ $score !== null ? 'score-value' : 'score-empty' }}">
                    {{ $score ?? '-' }}
                </span>
            </td>
        @endforeach

        @php
            $meta = $gradeService->componentSummary()[$gradingComponentId];
            $TS   = $gradeService->totalScore($assessments, $student->id);
            $PS   = $gradeService->percentageScore($TS, $meta['total_score']);
            $WS   = $gradeService->weightedScore($PS, $meta['weighted_score']);
        @endphp

        <td class="summary-cell ts" title="{{ $meta['component_label'] }} Total Score">{{ $TS }}</td>
        <td class="summary-cell ps" title="{{ $meta['component_label'] }} Percentage Score">{{ number_format($PS, 2) }}</td>
        <td class="summary-cell ws" title="{{ $meta['component_label'] }} Weighted Score">{{ number_format($WS, 2) }}</td>
    @endforeach

    @php $initialGrade = $gradeService->initialGrade($student->id); @endphp

    <td class="final-grade" title="{{ $hasTransmutedGrade ? 'Initial ' : null }} Grade">
        <div class="grade-badge initial">{{ number_format($initialGrade, 2) }}</div>
    </td>

    @if ($hasTransmutedGrade)
        <td class="final-grade" title="Transmuted Grade">
            <div class="grade-badge transmuted">
                {{ $gradeService->transmutedGrade($initialGrade) }}
            </div>
        </td>
    @endif
</tr>
