@php
    $gradeId = $record->id;
    $gradeGradingComponents = $record->orderedGradeGradingComponents;

    $groupedAssessments = $record->orderedGradeGradingComponents
    ->load(['gradingComponent', 'assessments'])
    ->groupBy(fn($ggc) => $ggc->gradingComponent?->label)
    ->map(fn($group) => $group->flatMap->assessments);

    // Calculate total columns needed for assessments
    $totalAssessmentColumns = $groupedAssessments->sum(fn($assessments) => $assessments->count() + 3);
    $totalColumns = $totalAssessmentColumns + 2; // +2 for Initial Grade and Quarterly Grade

@endphp

<div style="overflow-x: auto; width: 100%;">
    <table class="grades-table">
        <thead>
            <!-- ROW 1 -->
            <tr class="header-row">
                <td rowspan="3" class="frozen-column" style="vertical-align: middle; min-width: 180px;"><strong>STUDENT NAMES</strong></td>
                <td colspan="3">GRADE & SECTION:</td>
                <td colspan="6">Grade 10 - Ruby</td>
                <td colspan="2">TEACHER:</td>
                <td colspan="6">Mr. Juan Dela Cruz</td>
                <td colspan="{{ $totalColumns - 17 }}">SUBJECT: FILIPINO</td> {{-- Dynamic colspan --}}
            </tr>


            {{-- ROW 2: Components Label --}}
            <tr class="header-row">
                @foreach($groupedAssessments->keys() as $label)
                    @php
                        $currentAssessments = $groupedAssessments->get($label);
                        $colspan = $currentAssessments->count() + 3; // +3 for Total, PS, WS
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
            <tr class="header-row">
                <td class="frozen-column" style="text-align: right; font-size: 9px;">HIGHEST POSSIBLE SCORE</td>
                @foreach($groupedAssessments as $assessments)
                    @foreach($assessments as $item)
                        <td title="{{ $item->name }}">{{ $item->max_score }}</td>
                    @endforeach

                        {{-- TODO:: --}}

                        <td title="Total Score"><strong>Total</strong></td>
                        <td title="Percentage Score"><strong>100</strong></td>
                        <td title="Weighted Score"><strong>WS</strong></td>

                @endforeach
            </tr>

        </thead>

        <tbody>
            {{-- <!-- MALE SECTION -->
            <tr>
                <td class="gender-header frozen-column">MALE</td>
                <td colspan="31" class="gender-header"></td>
            </tr>
            <tr>
                <td class="learner-name frozen-column">Juan Dela Cruz</td>
                <td>89</td><td>87</td><td>91</td><td>88</td><td>90</td><td>92</td><td>87</td><td>91</td><td>93</td><td>85</td>
                <td>903</td><td>90.3</td><td>27.09</td>
                <td>95</td><td>94</td><td>96</td><td>90</td><td>98</td><td>97</td><td>92</td><td>95</td><td>94</td><td>98</td>
                <td>949</td><td>94.9</td><td>47.45</td>
                <td>90</td><td>90</td><td>18</td><td>92</td><td>92</td>
            </tr>
            <tr>
                <td class="learner-name frozen-column">Carlos Mendoza</td>
                <td>91</td><td>89</td><td>87</td><td>90</td><td>93</td><td>95</td><td>89</td><td>90</td><td>92</td><td>91</td>
                <td>907</td><td>90.7</td><td>27.21</td>
                <td>94</td><td>92</td><td>93</td><td>95</td><td>90</td><td>96</td><td>95</td><td>94</td><td>97</td><td>98</td>
                <td>944</td><td>94.4</td><td>47.2</td>
                <td>92</td><td>92</td><td>18.4</td><td>92</td><td>92</td>
            </tr>

            <!-- FEMALE SECTION -->
            <tr>
                <td class="gender-header frozen-column">FEMALE</td>
                <td colspan="31" class="gender-header"></td>
            </tr>
            <tr>
                <td class="learner-name frozen-column">Maria Santos</td>
                <td>94</td><td>93</td><td>95</td><td>92</td><td>94</td><td>93</td><td>96</td><td>92</td><td>95</td><td>94</td>
                <td>938</td><td>93.8</td><td>28.14</td>
                <td>97</td><td>98</td><td>96</td><td>95</td><td>94</td><td>97</td><td>98</td><td>95</td><td>99</td><td>100</td>
                <td>969</td><td>96.9</td><td>48.45</td>
                <td>95</td><td>95</td><td>19</td><td>96</td><td>96</td>
            </tr>
            <tr>
                <td class="learner-name frozen-column">Clara Villanueva</td>
                <td>90</td><td>91</td><td>89</td><td>90</td><td>92</td><td>91</td><td>93</td><td>92</td><td>94</td><td>90</td>
                <td>912</td><td>91.2</td><td>27.36</td>
                <td>94</td><td>96</td><td>97</td><td>95</td><td>93</td><td>92</td><td>94</td><td>95</td><td>97</td><td>94</td>
                <td>947</td><td>94.7</td><td>47.35</td>
                <td>93</td><td>93</td><td>18.6</td><td>93</td><td>93</td>
            </tr> --}}

        </tbody>
    </table>
</div>

<style>
    .grades-table {
        font-size: 12px;
        border-collapse: collapse;
        min-width: 1400px;
        background-color: #fff9db;
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
    .grades-table th {
        font-weight: 600;
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

    tbody tr:not(:has(.gender-header)) .frozen-column {
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
