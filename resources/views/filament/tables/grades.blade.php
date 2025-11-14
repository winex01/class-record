@php
    $schoolClass = $getOwnerRecord;
    $gradeGradingComponents = $record->orderedGradeGradingComponents;

    $groupedAssessments = $record->orderedGradeGradingComponents
        ->load(['gradingComponent', 'assessments'])
        ->groupBy(fn($ggc) => $ggc->gradingComponent?->label)
        ->map(fn($group) => $group->flatMap->assessments);

    // Calculate total columns needed for assessments
    $totalAssessmentColumns = $groupedAssessments->sum(fn($assessments) => $assessments->count() + 3);
    $totalColumns = $totalAssessmentColumns + 2; // +2 for Initial Grade and Quarterly Grade


    $students = $schoolClass->students->groupBy('gender');
@endphp

<div style="overflow-x: auto; width: 100%;">
    <table class="grades-table">
        <thead>
            <!-- ROW 1 -->
            <tr class="header-row">
                {{-- TODO::  --}}
                <td rowspan="3" class="frozen-column" style="vertical-align: middle; min-width: 180px;"><strong>STUDENT NAMES</strong></td>
                {{-- <td colspan="3">GRADE & SECTION:</td> --}}
                {{-- <td colspan="6">Grade 10 - Ruby</td> --}}
                {{-- <td colspan="2">TEACHER:</td> --}}
                {{-- <td colspan="6">Mr. Juan Dela Cruz</td> --}}
                {{-- <td colspan="4">SUBJECT: FILIPINO</td> --}}
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
                <td rowspan="3"><strong>Initial Grade</strong></td>
                <td rowspan="3"><strong>Quarterly Grade</strong></td>
            </tr>

            {{-- ROW 3: Assessment Numbers --}}
            <tr class="header-row">
                @foreach($groupedAssessments as $assessments)
                    @foreach($assessments as $item)
                        <td title="{{ $item->name }}">{{ $loop->iteration }}</td>
                    @endforeach
                    <td title="Total Score"><strong>Total</strong></td>
                    <td title="Percentage Score"><strong>PS</strong></td>
                    <td title="Weighted Score"><strong>WS</strong></td>
                @endforeach
            </tr>

            {{-- ROW 4: Max Scores --}}
            <tr class="highest-score">
                <td class="frozen-column" style="text-align: right; font-size: 9px;">HIGHEST POSSIBLE SCORE</td>
                @foreach($groupedAssessments as $assessments)
                    @php $totalScore = 0; @endphp
                    @foreach($assessments as $item)
                        @php $totalScore += $item->max_score; @endphp
                        <td title="{{ $item->name }}">{{ $item->max_score }}</td>
                    @endforeach

                    @php
                        $weightedScorePercentageLabel = null;
                        // Get the weighted_score from the first assessment's gradeGradingComponents relationship
                        if ($assessments->isNotEmpty()) {
                            $firstAssessment = $assessments->first();
                            $gradeGradingComponent = $firstAssessment->gradeGradingComponents->first();
                            $weightedScorePercentageLabel = $gradeGradingComponent->gradingComponent->weighted_score_percentage_label ?? null;
                        }
                    @endphp

                    {{-- TODO:: Total score --}}
                    <td title="Total Score"><strong>{{ $totalScore }}</strong></td>
                    <td title="Percentage Score"><strong>100</strong></td>
                    <td title="Weighted Score">
                        <strong>{{ $weightedScorePercentageLabel ?? '-' }}</strong>
                    </td>

                @endforeach
            </tr>
        </thead>

        <tbody>
            {{-- TODO:  --}}
            @foreach ($students as $gender => $studentByGender)
                <tr>
                    <td class="gender-header frozen-column">{{ $gender }}</td>
                    <td colspan="{{ $totalColumns }}" class="gender-header"></td>
                </tr>

                @foreach ($studentByGender as $student)
                    <tr>
                        <td class="learner-name frozen-column">{{ $student->full_name }}</td>
                        <td>89</td><td>87</td><td>91</td><td>88</td><td>90</td><td>92</td><td>87</td><td>91</td><td>93</td><td>85</td>
                        <td>903</td><td>90.3</td><td>27.09</td>
                        <td>95</td><td>94</td><td>96</td><td>90</td><td>98</td><td>97</td>
                        <td>92</td><td>95</td>
                    </tr>
                @endforeach

            @endforeach

        </tbody>
    </table>
</div>

<style>
    .grades-table {
        font-size: 12px;
        border-collapse: collapse;
        min-width: 1400px;
        background-color: #fff9db; /* This creates the yellow background */
        color: #000;
        margin: auto;
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
        background-color: #f9f9f9; /* Light gray for headers */
        color: #000;
    }
    .grades-table .highest-score {
        background-color: #f4f4e1; /* Light yellow for highest score row */
        font-weight: 600;
        color: #000;
    }
    .grades-table .gender-header {
        background-color: #e5e5e5; /* Medium gray for gender headers */
        font-weight: 600;
        text-align: left;
        color: #000;
    }

    /* Frozen first column styles */
    .grades-table .frozen-column {
        position: sticky;
        left: 0;
        z-index: 10;
        background-color: inherit;
    }

    /* Ensure frozen column maintains proper background colors */
    .header-row .frozen-column {
        background-color: #f9f9f9;
    }

    .highest-score .frozen-column {
        background-color: #f4f4e1;
    }

    .gender-header.frozen-column {
        background-color: #e5e5e5;
    }

    /* Student rows get the yellow background */
    tbody tr .frozen-column:not(.gender-header) {
        background-color: #fff9db;
    }

    /* Add shadow effect to frozen column for better visual separation */
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
</style>
