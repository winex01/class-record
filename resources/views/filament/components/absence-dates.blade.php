<div>
    @if($absences->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">No absence records found.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Attendance Date
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($absences as $absence)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                {{ $absence->date }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            Total: <strong>{{ $absences->count() }}</strong> day(s)
        </p>
    @endif
</div>
