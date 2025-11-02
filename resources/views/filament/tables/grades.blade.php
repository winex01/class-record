@php
    $wwCount = $data['written_works_count'];
    $ptCount = $data['performance_tasks_count'];
    $qaCount = $data['quarterly_assessment_count'];
    $totalColumns = 1 + $wwCount + 3 + $ptCount + 3 + $qaCount + 2 + 2; // name + WW + (Total,PS,WS) + PT + (Total,PS,WS) + QA + (PS,WS) + Initial + Quarterly
@endphp

<div style="overflow-x: auto; width: 100%;">
    <table class="grades-table">
        <thead>
            <!-- Header Row 1 -->
            <tr class="header-row">
                <td rowspan="3" style="vertical-align: middle; min-width: 180px;">
                    <strong>{{ $data['quarter'] }}</strong>
                </td>
                <td colspan="2">GRADE & SECTION:</td>
                <td colspan="{{ $wwCount + 1 }}">{{ $data['grade_section'] }}</td>
                <td colspan="2">TEACHER:</td>
                <td colspan="{{ $ptCount + 1 }}">{{ $data['teacher'] }}</td>
                <td colspan="{{ $qaCount + 4 }}" rowspan="2" style="vertical-align: middle;">
                    <strong>SUBJECT: {{ $data['subject'] }}</strong>
                </td>
            </tr>

            <!-- Header Row 2 -->
            <tr class="header-row">
                <td colspan="{{ $wwCount + 3 }}"><strong>WRITTEN WORKS ({{ $data['written_works_percentage'] }}%)</strong></td>
                <td colspan="{{ $ptCount + 3 }}"><strong>PERFORMANCE TASKS ({{ $data['performance_tasks_percentage'] }}%)</strong></td>
            </tr>

            <!-- Header Row 3 -->
            <tr class="header-row">
                @for($i = 0; $i < $wwCount; $i++)
                    <td>{{ $data['written_works_headers'][$i] ?? ($i + 1) }}</td>
                @endfor
                <td><strong>Total</strong></td>
                <td><strong>PS</strong></td>
                <td><strong>WS</strong></td>

                @for($i = 0; $i < $ptCount; $i++)
                    <td>{{ $data['performance_tasks_headers'][$i] ?? ($i + 1) }}</td>
                @endfor
                <td><strong>Total</strong></td>
                <td><strong>PS</strong></td>
                <td><strong>WS</strong></td>

                <td colspan="{{ $qaCount }}" style="vertical-align: middle;">
                    <strong>QUARTERLY<br>ASSESSMENT<br>({{ $data['quarterly_assessment_percentage'] }}%)</strong>
                </td>
                <td><strong>PS</strong></td>
                <td><strong>WS</strong></td>
                <td><strong>Initial</strong></td>
                <td><strong>Quarterly</strong></td>
            </tr>

            <!-- Highest Possible Score Row -->
            <tr class="highest-score">
                <td class="learner-name">HIGHEST POSSIBLE SCORE</td>
                @for($i = 0; $i < $wwCount; $i++)
                    <td>{{ $data['written_works_highest'][$i] ?? '' }}</td>
                @endfor
                <td>100.00</td>
                <td>{{ $data['written_works_percentage'] }}%</td>
                <td></td>

                @for($i = 0; $i < $ptCount; $i++)
                    <td>{{ $data['performance_tasks_highest'][$i] ?? '' }}</td>
                @endfor
                <td>100.00</td>
                <td>{{ $data['performance_tasks_percentage'] }}%</td>
                <td></td>

                @for($i = 0; $i < $qaCount; $i++)
                    <td>{{ $data['quarterly_assessment_highest'][$i] ?? '' }}</td>
                @endfor
                <td>100.00</td>
                <td>{{ $data['quarterly_assessment_percentage'] }}%</td>
                <td></td>
                <td></td>
            </tr>
        </thead>

        <tbody>
            <!-- Gender Header - MALE -->
            <tr>
                <td colspan="{{ $totalColumns }}" class="gender-header">MALE</td>
            </tr>

            <!-- Learner Rows -->
            @foreach($data['learners'] as $learner)
                @if($learner['gender'] === 'MALE')
                <tr>
                    <td class="learner-name">{{ $learner['name'] }}</td>

                    @foreach($learner['written_works'] as $score)
                        <td>{{ $score }}</td>
                    @endforeach
                    <td><strong>{{ $learner['ww_total'] }}</strong></td>
                    <td><strong>{{ $learner['ww_ps'] }}</strong></td>
                    <td><strong>{{ $learner['ww_ws'] }}</strong></td>

                    @foreach($learner['performance_tasks'] as $score)
                        <td>{{ $score }}</td>
                    @endforeach
                    <td><strong>{{ $learner['pt_total'] }}</strong></td>
                    <td><strong>{{ $learner['pt_ps'] }}</strong></td>
                    <td><strong>{{ $learner['pt_ws'] }}</strong></td>

                    @foreach($learner['quarterly_assessment'] as $score)
                        <td>{{ $score }}</td>
                    @endforeach
                    <td><strong>{{ $learner['qa_ps'] }}</strong></td>
                    <td><strong>{{ $learner['qa_ws'] }}</strong></td>
                    <td><strong>{{ $learner['initial_grade'] }}</strong></td>
                    <td><strong>{{ $learner['quarterly_grade'] }}</strong></td>
                </tr>
                @endif
            @endforeach

            <!-- Gender Header - FEMALE -->
            <tr>
                <td colspan="{{ $totalColumns }}" class="gender-header">FEMALE</td>
            </tr>

            @foreach($data['learners'] as $learner)
                @if($learner['gender'] === 'FEMALE')
                <tr>
                    <td class="learner-name">{{ $learner['name'] }}</td>

                    @foreach($learner['written_works'] as $score)
                        <td>{{ $score }}</td>
                    @endforeach
                    <td><strong>{{ $learner['ww_total'] }}</strong></td>
                    <td><strong>{{ $learner['ww_ps'] }}</strong></td>
                    <td><strong>{{ $learner['ww_ws'] }}</strong></td>

                    @foreach($learner['performance_tasks'] as $score)
                        <td>{{ $score }}</td>
                    @endforeach
                    <td><strong>{{ $learner['pt_total'] }}</strong></td>
                    <td><strong>{{ $learner['pt_ps'] }}</strong></td>
                    <td><strong>{{ $learner['pt_ws'] }}</strong></td>

                    @foreach($learner['quarterly_assessment'] as $score)
                        <td>{{ $score }}</td>
                    @endforeach
                    <td><strong>{{ $learner['qa_ps'] }}</strong></td>
                    <td><strong>{{ $learner['qa_ws'] }}</strong></td>
                    <td><strong>{{ $learner['initial_grade'] }}</strong></td>
                    <td><strong>{{ $learner['quarterly_grade'] }}</strong></td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>

<style>
    .grades-table {
        font-size: 12px;
        border-collapse: collapse;
        min-width: 1400px;
        background-color: #fff9db; /* light yellow */
        color: #000; /* black text */
        margin: auto;
    }
    .grades-table th,
    .grades-table td {
        border: 1px solid;
        @apply border-gray-300 dark:border-gray-600;
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
        @apply bg-gray-100 dark:bg-gray-800;
        color: #000;
    }
    .grades-table .highest-score {
        @apply bg-gray-50 dark:bg-gray-900;
        font-weight: 600;
        color: #000;
    }
    .grades-table .gender-header {
        @apply bg-gray-200 dark:bg-gray-700;
        font-weight: 600;
        text-align: left;
        color: #000;
    }
</style>
