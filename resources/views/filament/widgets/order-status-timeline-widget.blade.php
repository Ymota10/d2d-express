<x-filament-widgets::widget>
    <x-filament::section>
        
        {{-- HORIZONTAL ORDER TRACKER --}}
        <h3 class="text-lg font-bold mb-6 text-center">Status Tracking</h3>

        @php
            $steps = $this->getSteps();
            $timeline = $this->getTimeline(); // 🟢 new array method returning full event logs
        @endphp

        {{-- Full-width horizontal tracker --}}
        <div class="relative flex items-center justify-between w-full px-16 gap-6 mb-12">
            {{-- Gray base line --}}
            <div class="absolute top-1/2 left-0 w-full h-1 bg-gray-200 -z-10"></div>

            {{-- Green progress line --}}
            <div
                class="absolute top-1/2 left-0 h-1 bg-green-500 -z-10 transition-all duration-700"
                style="width: {{ (count(array_filter($steps, fn($s) => $s['completed'])) - 1) / max(count($steps) - 1, 1) * 100 }}%;"
            ></div>

            @foreach ($steps as $index => $step)
                <div class="flex flex-col items-center text-center flex-1 mx-2">
                    {{-- Step Circle --}}
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full border-2
                        {{ $step['completed'] ? 'bg-green-500 border-green-500 text-green-700' : 'border-gray-300 text-gray-400' }}"
                    >
                        @if ($step['completed'])
                            {{-- ✅ Green checkmark --}}
                            <x-heroicon-o-check class="w-6 h-6 text-green-700" />
                        @else
                            <span class="text-sm font-semibold">{{ $index + 1 }}</span>
                        @endif
                    </div>

                    {{-- Label --}}
                    <div class="mt-2 text-sm font-semibold text-gray-800">
                        {{ $step['label'] }}
                    </div>

                    {{-- Timestamp --}}
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $step['time'] }}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- VERTICAL TIMELINE --}}
<h3 class="text-lg font-bold mb-4 text-center">Timeline</h3>

<div class="ml-8 space-y-6">
    @foreach ($timeline as $event)
        <div class="pl-2 border-l-2 border-gray-200">
            {{-- Event Title --}}
            <p class="font-semibold text-sm text-gray-800 leading-snug">
                {{ $event['title'] }}
            </p>

            {{-- Optional Details --}}
            @if (!empty($event['details']))
                <p class="text-xs text-gray-600 mt-1 leading-tight">
                    {{ $event['details'] }}
                </p>
            @endif

            {{-- Timestamp --}}
            @if (!empty($event['date']))
                <p class="text-xs text-gray-500 mt-2 leading-tight">
                    {{ $event['date'] }}
                </p>
            @endif
        </div>
    @endforeach
</div>

    </x-filament::section>
</x-filament-widgets::widget>
