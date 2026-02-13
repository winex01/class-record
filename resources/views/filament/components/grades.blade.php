{{-- grades.blade.php --}}
<div class="grades-wrapper">
    <div class="grades-scroll-container">
        <table class="grades-table">
            <thead>
                <!-- ROW 1: Header Info -->
                <tr class="info-row">
                    <th rowspan="3" class="frozen-column student-column max-score-label">
                        <div class="column-header label-content">
                            <svg class="header-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Student Name</span>
                        </div>
                    </th>
                    @php
                        $colspan = max(1, (int)$totalColumns / 3);
                    @endphp
                    <th colspan="{{ $colspan }}" class="meta-info">
                        <span class="info-label">Grading Period</span>
                        <span class="info-value">{{ $record->grading_period }}</span>
                    </th>
                    <th colspan="{{ $colspan }}" class="meta-info">
                        <span class="info-label">Subject</span>
                        <span class="info-value">{{ $schoolClass->name }}</span>
                    </th>
                    <th colspan="{{ $colspan + 99 }}" class="meta-info">
                        <span class="info-label">Year & Section</span>
                        <span class="info-value">{{ str_replace(',', ', ', $schoolClass->year_section) }}</span>
                    </th>
                </tr>

                {{-- ROW 2: Components Label --}}
                <tr class="component-row">
                    @foreach($groupedAssessments->keys() as $label)
                        @php
                            $currentAssessments = $groupedAssessments->get($label);
                            $colspan = $currentAssessments->count() + 3;
                        @endphp
                        <th colspan="{{ $colspan }}" class="component-header">
                            <div class="component-badge">{{ $label }}</div>
                        </th>
                    @endforeach

                    <th rowspan="3" class="grade-column">
                        <div class="grade-header">
                            @if ($hasTransmutedGrade)
                                Initial<br>
                            @endif
                            Grade
                        </div>
                    </th>

                    @if ($hasTransmutedGrade)
                        <th rowspan="3" class="grade-column transmuted">
                            <div class="grade-header">
                                Transmuted<br>Grade
                            </div>
                        </th>
                    @endif
                </tr>

                {{-- ROW 3: Assessment Numbers --}}
                <tr class="assessment-row">
                    @foreach($groupedAssessments as $assessments)
                        @foreach($assessments as $item)
                            <th class="assessment-number" title="{{ $item->name }}">
                                <span class="assessment-badge">{{ $loop->iteration }}</span>
                            </th>
                        @endforeach
                        <th class="summary-col ts" title="Total Score">TS</th>
                        <th class="summary-col ps" title="Percentage Score">PS</th>
                        <th class="summary-col ws" title="Weighted Score">WS</th>
                    @endforeach
                </tr>

                {{-- ROW 4: Max Scores --}}
                <tr class="max-score-row">
                    <th class="frozen-column max-score-label">
                        <div class="label-content">
                            <svg class="label-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Highest Possible Score
                        </div>
                    </th>
                    @foreach($groupedAssessments as $label => $assessments)
                        @foreach($assessments as $item)
                            <th class="max-score-value" title="{{ $item->name }} Max Score">
                                {{ $item->max_score }}
                            </th>
                        @endforeach

                        @php
                            $meta = $assessmentMeta[$label];
                        @endphp

                        <th class="summary-value ts" title="{{ $meta['component_label'] }} Total Score">
                            {{ $meta['total_score'] }}
                        </th>
                        <th class="summary-value ps" title="{{ $meta['component_label'] }} Percentage Score">
                            {{ $percentageScore }}
                        </th>
                        <th class="summary-value ws" title="{{ $meta['component_label'] }} Weighted Score">
                            {{ $meta['weighted_score_label'] ?? '-' }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($students as $gender => $studentByGender)
                    <tr class="gender-divider">
                        <td class="frozen-column gender-label">
                            <div class="gender-badge">
                                <svg class="gender-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ $gender }}
                            </div>
                        </td>
                        <td colspan="{{ $totalColumns }}" class="gender-spacer"></td>
                    </tr>

                    @foreach ($studentByGender as $student)
                        @php
                            $studentInitialGrade = 0;
                        @endphp
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

                            @foreach($groupedAssessments as $label => $assessments)
                                @php
                                    $TS = 0;
                                    $meta = $assessmentMeta[$label];
                                @endphp

                                @foreach($assessments as $assessment)
                                    @php
                                        $score = $studentScores[$student->id][$assessment->id] ?? null;
                                        $TS += $score ?? 0;
                                    @endphp
                                    <td class="score-cell" title="{{ $assessment->name }} Raw Score">
                                        @if($score !== null)
                                            <span class="score-value">{{ $score }}</span>
                                        @else
                                            <span class="score-empty">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                @php
                                    $PS_raw = round(($TS / $meta['total_score']) * $percentageScore, 2);
                                    $WS_raw = round($PS_raw * ($meta['weighted_score'] / 100), 2);
                                    $studentInitialGrade += $WS_raw;

                                    $PS_display = number_format($PS_raw, 2);
                                    $WS_display = number_format($WS_raw, 2);
                                @endphp

                                <td class="summary-cell ts" title="{{ $meta['component_label'] }} Total Score">
                                    {{ $TS }}
                                </td>
                                <td class="summary-cell ps" title="{{ $meta['component_label'] }} Percentage Score">
                                    {{ $PS_display }}
                                </td>
                                <td class="summary-cell ws" title="{{ $meta['component_label'] }} Weighted Score">
                                    {{ $WS_display }}
                                </td>
                            @endforeach

                            @php
                                $studentInitialGrade = number_format(round($studentInitialGrade, 2), 2);
                            @endphp

                            <td class="final-grade" title="Initial Grade">
                                <div class="grade-badge initial">{{ $studentInitialGrade }}</div>
                            </td>

                            @if ($hasTransmutedGrade)
                                <td class="final-grade" title="Transmuted Grade">
                                    <div class="grade-badge transmuted">
                                        {{ App\Services\GradeCalculation::getTransmutedGrade($schoolClass, $studentInitialGrade) }}
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
    /* ===== FROZEN HEADERS + FROZEN COLUMN FIX ===== */
    /* Enable vertical scrolling */
    .grades-wrapper {
        max-height: 80vh;
        display: flex;
        flex-direction: column;
    }

    .grades-scroll-container {
        overflow-x: auto;
        overflow-y: auto;
        position: relative;
        flex: 1;
    }

    /* Make thead sticky vertically */
    .grades-table thead {
        position: sticky;
        top: 0;
        z-index: 30;
    }

    /* Frozen column base styles (applies to both student name and max score label) */
    .frozen-column {
        position: sticky;
        left: 0;
        z-index: 25;
        background: white;
        box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    }

    .dark .frozen-column {
        background: #111827;
        box-shadow: 2px 0 4px rgba(0, 0, 0, 0.3);
    }

    /* Frozen columns in header - higher z-index */
    thead .frozen-column {
        z-index: 35;
    }

    /* Top-most priority for the very first header cell ("Student Name") */
    .student-column {
        z-index: 40 !important;           /* highest priority */
        background: #f3f4f6 !important;
        width: 220px;
        min-width: 220px;
        max-width: 220px;
    }

    .dark .student-column {
        background: #1f2937 !important;
    }

    /* Make sure "Highest Possible Score" cell also stays frozen horizontally */
    .max-score-label {
        position: sticky !important;
        left: 0 !important;
        z-index: 35 !important;
        background: #fef3c7 !important;
    }

    .dark .max-score-label {
        background: #451a03 !important;
    }

    /* ===== FIX HEADER TRANSPARENCY - FORCE SOLID OPAQUE BACKGROUNDS ===== */
    .grades-table thead th {
        position: relative;
        isolation: isolate;
    }

    /* Info row - solid background */
    .info-row th {
        background: #f3f4f6 !important;
        border-bottom: 2px solid #e5e7eb;
        padding: 1rem;
        font-weight: 500;
    }

    .dark .info-row th {
        background: #1f2937 !important;
        border-bottom: 2px solid #374151;
    }

    /* Component row - solid background */
    .component-row th {
        background: #e5e7eb !important;
        border-bottom: 1px solid #d1d5db;
        padding: 0.75rem;
    }

    .dark .component-row th {
        background: #111827 !important;
        border-bottom: 1px solid #374151;
    }

    /* Alternating component backgrounds - modern and subtle */
    .component-row th.component-header:nth-child(odd) {
        background: linear-gradient(to bottom, #f0f9ff 0%, #e0f2fe 100%) !important;
    }

    .component-row th.component-header:nth-child(even) {
        background: linear-gradient(to bottom, #f5f3ff 0%, #ede9fe 100%) !important;
    }

    .dark .component-row th.component-header:nth-child(odd) {
        background: linear-gradient(to bottom, #0c4a6e 0%, #075985 100%) !important;
    }

    .dark .component-row th.component-header:nth-child(even) {
        background: linear-gradient(to bottom, #4c1d95 0%, #5b21b6 100%) !important;
    }

    .component-badge {
        display: inline-flex;
        padding: 0.375rem 0.75rem;
        background: transparent;
        color: #1e40af;
        border-radius: 0;
        font-weight: 700;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        box-shadow: none;
    }

    .dark .component-badge {
        background: transparent;
        color: #93c5fd;
        box-shadow: none;
    }

    /* Assessment row - solid background */
    .assessment-row th {
        background: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb;
        padding: 0.5rem;
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 500;
    }

    .dark .assessment-row th {
        background: #111827 !important;
        border-bottom: 1px solid #374151;
        color: #9ca3af;
    }

    /* Max score row - solid background */
    .max-score-row th {
        background: #fef3c7 !important;
        border-bottom: 2px solid #fbbf24;
        padding: 0.625rem;
        font-weight: 600;
        color: #92400e;
    }

    .dark .max-score-row th {
        background: #451a03 !important;
        border-bottom: 2px solid #d97706;
        color: #fcd34d;
    }

    /* Grade columns in headers - solid backgrounds */
    thead .grade-column {
        background: #fde68a !important;
        font-weight: 600;
        color: #92400e;
    }

    .dark thead .grade-column {
        background: #78350f !important;
        color: #fcd34d;
    }

    thead .grade-column.transmuted {
        background: #bfdbfe !important;
        color: #1e40af;
    }

    .dark thead .grade-column.transmuted {
        background: #1e40af !important;
        color: #93c5fd;
    }

    /* Add vertical spacing with subtle shadow on WS columns */
    .assessment-row th.summary-col.ws,
    .max-score-row th.summary-value.ws {
        position: relative;
    }

    .assessment-row th.summary-col.ws::after,
    .max-score-row th.summary-value.ws::after {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 8px;
        background: linear-gradient(to right, transparent, rgba(59, 130, 246, 0.1));
        pointer-events: none;
    }

    .dark .assessment-row th.summary-col.ws::after,
    .dark .max-score-row th.summary-value.ws::after {
        background: linear-gradient(to right, transparent, rgba(59, 130, 246, 0.2));
    }

    /* ===== PRESERVED ORIGINAL STYLES BELOW ===== */
    .avatar-image {
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        object-fit: cover;
        flex-shrink: 0;
    }

    .avatar-initials {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        flex-shrink: 0;
    }

    .grades-wrapper {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        overflow: hidden;
    }

    .dark .grades-wrapper {
        background: rgb(var(--gray-900));
    }

    .grades-table {
        width: 100%;
        min-width: 1200px;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
        line-height: 1.25rem;
    }

    .meta-info {
        text-align: center;
    }

    .info-label {
        display: block;
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }

    .dark .info-label {
        color: #9ca3af;
    }

    .info-value {
        display: block;
        font-size: 0.875rem;
        color: #111827;
        font-weight: 600;
    }

    .dark .info-value {
        color: #f3f4f6;
    }

    .assessment-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 0.375rem;
        font-weight: 600;
    }

    .dark .assessment-badge {
        background: #1e3a8a;
        color: #93c5fd;
    }

    .summary-col {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
    }

    .summary-col.ts { color: #059669; }
    .summary-col.ps { color: #7c3aed; }
    .summary-col.ws { color: #dc2626; }

    .dark .summary-col.ts { color: #34d399; }
    .dark .summary-col.ps { color: #a78bfa; }
    .dark .summary-col.ws { color: #f87171; }

    .max-score-label .label-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .label-icon {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
    }

    .max-score-value {
        color: #78350f;
    }

    .dark .max-score-value {
        color: #fcd34d;
    }

    .summary-value {
        font-weight: 700;
    }

    .summary-value.ts {
        background: #f3fdf7;
        color: #047857;
    }

    .summary-value.ps {
        background: #f9f5ff;
        color: #6d28d9;
    }

    .summary-value.ws {
        background: #fff5f5;
        color: #b91c1c;
    }

    .dark .summary-value.ts {
        background: rgba(16, 185, 129, 0.18);
        color: #6ee7b7;
    }

    .dark .summary-value.ps {
        background: rgba(139, 92, 246, 0.18);
        color: #c4b5fd;
    }

    .dark .summary-value.ws {
        background: rgba(239, 68, 68, 0.18);
        color: #fca5a5;
    }

    /* Grade Columns */
    .grade-column {
        font-weight: 600;
        color: #92400e;
    }

    .dark .grade-column {
        color: #fcd34d;
    }

    .grade-column.transmuted {
        color: #1e40af;
    }

    .dark .grade-column.transmuted {
        color: #93c5fd;
    }

    .grade-header {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        line-height: 1.2;
    }

    .column-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #111827;
    }

    .dark .column-header {
        color: #f3f4f6;
    }

    .header-icon {
        width: 1.25rem;
        height: 1.25rem;
        color: #6b7280;
    }

    .dark .header-icon {
        color: #9ca3af;
    }

    /* Body Styles */
    .gender-divider {
        background: #f3f4f6;
    }

    .dark .gender-divider {
        background: #1f2937;
    }

    .gender-label {
        padding: 0.75rem 1rem;
    }

    .gender-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.75rem;
        background: #4f46e5;
        color: white;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: capitalize;
    }

    .gender-icon {
        width: 1rem;
        height: 1rem;
    }

    .gender-spacer {
        background: #f3f4f6;
    }

    .dark .gender-spacer {
        background: #1f2937;
    }

    /* hover animation */
    .student-row,
    .student-row .frozen-column {
        transition: background-color 0.05s ease-in-out;
    }

    .student-row {
        border-bottom: 1px solid #f3f4f6;
    }

    .dark .student-row {
        border-bottom: 1px solid #1f2937;
    }

    .student-row:hover {
        background: #f0f9ff !important;
    }

    .dark .student-row:hover {
        background: #1e3a8a !important;
    }

    .student-row:hover .frozen-column {
        background: #f0f9ff !important;
    }

    .dark .student-row:hover .frozen-column {
        background: #1e3a8a !important;
    }

    .student-name {
        padding: 0.75rem 1rem;
        font-weight: 500;
        color: #111827;
    }

    .dark .student-name {
        color: #f3f4f6;
    }

    .name-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    /* Score Cells */
    .grades-table td {
        padding: 0.625rem;
        text-align: center;
        border-right: 1px solid #f3f4f6;
    }

    .dark .grades-table td {
        border-right: 1px solid #1f2937;
    }

    .score-cell {
        font-weight: 500;
        color: #374151;
    }

    .dark .score-cell {
        color: #d1d5db;
    }

    .score-value {
        color: #111827;
    }

    .dark .score-value {
        color: #f3f4f6;
    }

    .score-empty {
        color: #d1d5db;
        font-weight: 400;
    }

    .dark .score-empty {
        color: #4b5563;
    }

    .summary-cell {
        font-weight: 600;
        border-left: 1px solid #e5e7eb;
    }

    .dark .summary-cell {
        border-left: 1px solid #374151;
    }

    .summary-cell.ts {
        background: #f8fffb;
        color: #047857;
    }

    .summary-cell.ps {
        background: #fcfaff;
        color: #6d28d9;
    }

    .summary-cell.ws {
        background: #fff9f9;
        color: #b91c1c;
        position: relative;
    }

    /* Add subtle shadow separator after WS in body rows */
    .summary-cell.ws::after {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 8px;
        background: linear-gradient(to right, transparent, rgba(59, 130, 246, 0.08));
        pointer-events: none;
    }

    .dark .summary-cell.ts {
        background: rgba(16, 185, 129, 0.12);
        color: #86efac;
    }

    .dark .summary-cell.ps {
        background: rgba(139, 92, 246, 0.12);
        color: #c4b5fd;
    }

    .dark .summary-cell.ws {
        background: rgba(239, 68, 68, 0.12);
        color: #fca5a5;
    }

    .dark .summary-cell.ws::after {
        background: linear-gradient(to right, transparent, rgba(59, 130, 246, 0.15));
    }

    .final-grade {
        padding: 0.5rem;
    }

    .grade-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        font-weight: 700;
        font-size: 0.875rem;
        min-width: 3.5rem;
    }

    .grade-badge.initial {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
    }

    .grade-badge.transmuted {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .grades-table {
            font-size: 0.75rem;
        }

        .student-column {
            width: 150px;
            min-width: 150px;
            max-width: 150px;
        }

        .avatar {
            width: 1.5rem;
            height: 1.5rem;
            font-size: 0.625rem;
        }
    }

    /* Print Styles */
    @media print {
        .grades-wrapper {
            box-shadow: none;
        }

        .student-row {
            break-inside: avoid;
        }
    }
</style>
