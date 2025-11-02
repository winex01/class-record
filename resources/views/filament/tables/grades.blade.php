<div style="overflow-x: auto; width: 100%;">
    <style>
        .grades-table {
            font-size: 12px;
            border-collapse: collapse;
            min-width: 1400px;
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
        }
        .grades-table .highest-score {
            @apply bg-gray-50 dark:bg-gray-900;
            font-weight: 600;
        }
        .grades-table .gender-header {
            @apply bg-gray-200 dark:bg-gray-700;
            font-weight: 600;
            text-align: left;
        }
    </style>

    <table class="grades-table">
        <thead>
            <!-- Header Row 1 -->
            <tr class="header-row">
                <td rowspan="3" style="vertical-align: middle; min-width: 180px;">
                    <strong>{{ $data['quarter'] }}</strong>
                </td>
                <td colspan="2">GRADE & SECTION:</td>
                <td colspan="11"></td>
                <td colspan="2">TEACHER:</td>
                <td colspan="11"></td>
                <td colspan="5" rowspan="2" style="vertical-align: middle;">
                    <strong>SUBJECT: {{ $data['subject'] }}</strong>
                </td>
            </tr>

            <!-- Header Row 2 -->
            <tr class="header-row">
                <td colspan="13"><strong>WRITTEN WORKS (30%)</strong></td>
                <td colspan="13"><strong>PERFORMANCE TASKS (50%)</strong></td>
            </tr>

            <!-- Header Row 3 -->
            <tr class="header-row">
                <td>1</td>
                <td>2</td>
                <td>3</td>
                <td>4</td>
                <td>5</td>
                <td>6</td>
                <td>7</td>
                <td>8</td>
                <td>9</td>
                <td>10</td>
                <td><strong>Total</strong></td>
                <td><strong>PS</strong></td>
                <td><strong>WS</strong></td>
                <td>1</td>
                <td>2</td>
                <td>3</td>
                <td>4</td>
                <td>5</td>
                <td>6</td>
                <td>7</td>
                <td>8</td>
                <td>9</td>
                <td>10</td>
                <td><strong>Total</strong></td>
                <td><strong>PS</strong></td>
                <td><strong>WS</strong></td>
                <td colspan="3" style="vertical-align: middle;">
                    <strong>QUARTERLY<br>ASSESSMENT<br>(20%)</strong>
                </td>
                <td><strong>Initial</strong></td>
                <td><strong>Quarterly</strong></td>
            </tr>

            <!-- Highest Possible Score Row -->
            <tr class="highest-score">
                <td class="learner-name">HIGHEST POSSIBLE SCORE</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>100.00</td>
                <td>30%</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>100.00</td>
                <td>50%</td>
                <td></td>
                <td>100.00</td>
                <td>20%</td>
                <td></td>
                <td></td>
            </tr>
        </thead>

        <tbody>
            <!-- Gender Header - MALE -->
            <tr>
                <td colspan="32" class="gender-header">MALE</td>
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
                    <td>{{ $learner['quarterly_assessment'] }}</td>
                    <td><strong>{{ $learner['qa_ps'] }}</strong></td>
                    <td><strong>{{ $learner['qa_ws'] }}</strong></td>
                    <td><strong>{{ $learner['initial_grade'] }}</strong></td>
                    <td><strong>{{ $learner['quarterly_grade'] }}</strong></td>
                </tr>
                @endif
            @endforeach

            <!-- Gender Header - FEMALE -->
            <tr>
                <td colspan="32" class="gender-header">FEMALE</td>
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
                    <td>{{ $learner['quarterly_assessment'] }}</td>
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
    </div>
</div>
