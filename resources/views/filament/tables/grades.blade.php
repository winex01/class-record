{{-- All variables are now passed from the action --}}
<div style="overflow-x: auto; width: 100%;">
    <table class="grades-table">
        <thead>
            <!-- ROW 1 -->
            <tr class="header-row">
                <td rowspan="3" class="frozen-column" style="vertical-align: middle; min-width: 180px;">
                    <strong>STUDENT NAMES</strong>
                </td>
                @php
                    $colspan = (int)$totalColumns / 3;
                @endphp
                <td colspan="{{ $colspan }}">GRADING PERIOD: {{ $record->grading_period }}</td>
                <td colspan="{{ $colspan }}">SUBJECT: {{ $schoolClass->name }}</td>
                <td colspan="{{ $colspan + 99 }}">TAGS: {{ implode(', ', $schoolClass->tags) }}</td>
            </tr>

            {{-- ROW 2: Components Label --}}
            <tr class="header-row">
                @foreach($groupedAssessments->keys() as $label)
                    @php
                        $currentAssessments = $groupedAssessments->get($label);
                        $colspan = $currentAssessments->count() + 3;
                    @endphp
                    <td colspan="{{ $colspan }}"><strong>{{ $label }}</strong></td>
                @endforeach
                <td rowspan="3"><strong>Initial<br>Grade</strong></td>
                <td rowspan="3" title="Transmuted Grade"><strong>Quarterly<br>Grade</strong></td>
            </tr>

            {{-- ROW 3: Assessment Numbers --}}
            <tr class="header-row">
                @foreach($groupedAssessments as $assessments)
                    @foreach($assessments as $item)
                        <td title="{{ $item->name }}">{{ $loop->iteration }}</td>
                    @endforeach
                    <td title="Total Score"><strong>TS</strong></td>
                    <td title="Percentage Score"><strong>PS</strong></td>
                    <td title="Weighted Score"><strong>WS</strong></td>
                @endforeach
            </tr>

            {{-- ROW 4: Max Scores --}}
            <tr class="highest-score">
                <td class="frozen-column" style="text-align: right; font-size: 9px;">HIGHEST POSSIBLE SCORE</td>
                @foreach($groupedAssessments as $assessments)
                    @php
                        $totalScore = 0;
                    @endphp
                    @foreach($assessments as $item)
                        @php
                            $totalScore += $item->max_score;
                        @endphp
                        <td title="{{ $item->name }} Max Score">{{ $item->max_score }}</td>
                    @endforeach

                    @php
                        $weightedScorePercentageLabel = null;
                        $firstAssessment = $assessments->first();
                        $gradeGradingComponent = $firstAssessment->gradeGradingComponents->first();
                        $weightedScorePercentageLabel = $gradeGradingComponent->gradingComponent->weighted_score_percentage_label ?? null;
                        $componentLabel = $gradeGradingComponent->gradingComponent->name;
                    @endphp

                    <td title="{{ $componentLabel }} Total Score"><strong>{{ $totalScore }}</strong></td>
                    <td title="{{ $componentLabel }} Percentage Score"><strong>{{ $percentageScore }}</strong></td>
                    <td title="{{ $componentLabel }} Weighted Score">
                        <strong>{{ $weightedScorePercentageLabel ?? '-' }}</strong>
                    </td>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach ($students as $gender => $studentByGender)
                <tr>
                    <td class="gender-header frozen-column">{{ $gender }}</td>
                    <td colspan="{{ $totalColumns }}" class="gender-header"></td>
                </tr>

                @foreach ($studentByGender as $student)
                    @php
                        $studentInitialGrade = 0;
                    @endphp
                    <tr>
                        <td class="learner-name frozen-column">{{ $student->full_name }}</td>

                        @foreach($groupedAssessments as $assessments)
                            @php
                                $TS = 0;
                                $assessmentTotalScore = 0;

                                $firstAssessment = $assessments->first();
                                $gradeGradingComponent = $firstAssessment->gradeGradingComponents->first();
                                $weightedScore = $gradeGradingComponent->gradingComponent->weighted_score;
                                $componentLabel = $gradeGradingComponent->gradingComponent->name;
                            @endphp

                            @foreach($assessments as $assessment)
                                @php
                                    $assessmentTotalScore += $assessment->max_score;
                                    $ass = $assessment->students()->where('student_id', $student->id)->first();
                                    $score = $ass->pivot->score ?? null;
                                    $TS += $score ?? 0;
                                @endphp
                                <td title="{{ $assessment->name }} Raw Score">{{ $score }}</td>
                            @endforeach

                            @php
                                // Raw value for calculations
                                $PS_raw = round(($TS / $assessmentTotalScore) * $percentageScore, 2);
                                $WS_raw = round($PS_raw * ($weightedScore / 100), 2);
                                $studentInitialGrade += $WS_raw;

                                // Formatted value for display
                                $PS_display = number_format($PS_raw, 2);
                                $WS_display = number_format($WS_raw, 2);
                            @endphp

                            <td title="{{ $componentLabel }} Total Score">{{ $TS }}</td>
                            <td title="{{ $componentLabel }} Percentage Score"><strong>{{ $PS_display }}</strong></td>
                            <td title="{{ $componentLabel }} Weighted Score"><strong>{{ $WS_display }}</strong></td>
                        @endforeach

                        @php
                            $studentInitialGrade = number_format(round($studentInitialGrade, 2), 2);
                        @endphp

                        <td title="Initial Grade"><strong>{{ $studentInitialGrade }}</strong></td>
                        <td title="Transmuted Grade"></td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

<style>
    /* Z-index fix: Ensure Filament select dropdown appears above table */
    :root {
        --table-z-index: 1;
        --dropdown-z-index: 50;
    }

    .grades-table {
        font-size: 12px;
        border-collapse: collapse;
        min-width: 1400px;
        background-color: #fff9db;
        color: #000;
        margin: auto;
        position: relative;
        z-index: var(--table-z-index);
    }
    .grades-table th,
    .grades-table td {
        border: 1px solid #444;
        padding: 4px 8px;
        text-align: center;
        white-space: nowrap;
    }
    .grades-table .learner-name {
        text-align: left;
        font-weight: 500;
    }
    .grades-table .header-row {
        background-color: #f9f9f9;
        color: #000;
    }
    .grades-table .highest-score {
        background-color: #f4f4e1;
        font-weight: 600;
        color: #000;
    }
    .grades-table .gender-header {
        background-color: #e5e5e5;
        font-weight: 600;
        text-align: left;
        color: #000;
    }
    .grades-table .frozen-column {
        position: sticky;
        left: 0;
        z-index: var(--table-z-index);
        background-color: inherit;
    }
    .header-row .frozen-column {
        background-color: #f9f9f9;
    }
    .highest-score .frozen-column {
        background-color: #f4f4e1;
    }
    .gender-header.frozen-column {
        background-color: #e5e5e5;
    }
    tbody tr .frozen-column:not(.gender-header) {
        background-color: #fff9db;
    }
    .frozen-column::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(to right, rgba(0,0,0,0.1), transparent);
        pointer-events: none;
    }
    tbody tr:hover {
        background-color: #d1ecf1 !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.2s ease-in-out;
    }
    tbody tr:hover .frozen-column:not(.gender-header) {
        background-color: #d1ecf1 !important;
    }
</style>
