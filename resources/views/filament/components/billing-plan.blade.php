@php
    $monthlyPrice = config('app.monthly_plan');
    $annualPrice = config('app.annual_plan');
    $contacts = config('app.contact_us');
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <!-- Monthly -->
    <div class="p-5 rounded-2xl border bg-white dark:bg-gray-900 shadow-sm">
        <div class="text-lg font-semibold mb-1">Monthly Plan</div>
        <div class="text-2xl font-bold text-primary-600">₱{{ $monthlyPrice }}</div>
        <div class="text-sm text-gray-500 mb-4">per month</div>

        <ul class="text-sm space-y-1 text-gray-600 dark:text-gray-400">
            <li>✔ Full access</li>
            <li>✔ Regular updates</li>
            <li>✔ Basic support</li>
        </ul>
    </div>

    <!-- Annual -->
    <div class="p-5 rounded-2xl border-2 border-primary-500 bg-white dark:bg-gray-900 shadow-md relative">
        <span class="absolute top-2 right-2 text-xs bg-primary-500 text-white px-2 py-1 rounded">
            BEST VALUE
        </span>

        <div class="text-lg font-semibold mb-1">Annual Plan</div>
        <div class="text-2xl font-bold text-primary-600">₱{{ $annualPrice }}</div>
        <div class="text-sm text-gray-500 mb-4">per year</div>

        <ul class="text-sm space-y-1 text-gray-600 dark:text-gray-400">
            <li>✔ All in Monthly Plan</li>
            <li>✔ Priority support</li>
            <li>✔ Save more</li>
        </ul>
    </div>

</div>

<!-- Contact Us -->
<div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
    For inquiries:
    <br>
    @foreach ($contacts as $contact)
        <a href="{{ $contact }}"
           class="text-primary-600 hover:text-primary-800 hover:underline transition-colors duration-200"
           target="_blank" rel="noopener noreferrer">
           {{ $contact }}
        </a><br>
    @endforeach
</div>
