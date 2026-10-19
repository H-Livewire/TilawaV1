<div class="mushaf-page-inner" dir="rtl">
    <header class="mushaf-running-title">{{ collect($page['ayahs'])->pluck('surahArabicName')->unique()->implode(' · ') }}</header>
    <div class="mushaf-arabic font-arabic">
        @foreach ($page['ayahs'] as $ayah)
            @php $key = $ayah['surahNumber'].':'.$ayah['number']; @endphp
            @if ($ayah['number'] === 1)
                <h2 class="mushaf-surah-title">{{ $ayah['surahArabicName'] }}</h2>
                @if (! in_array($ayah['surahNumber'], [1, 9], true))
                    <p class="mushaf-bismillah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
                @endif
            @endif
            <span data-verse="{{ $key }}">
                @if (isset($words[$key]))
                    @foreach ($words[$key] as $i => $word)
                        <span class="word-token" data-word="{{ json_encode(array_merge($word, ['surah' => $ayah['surahNumber'], 'surahName' => $ayah['surahName'], 'ayah' => $ayah['number'], 'wordKey' => $key.':'.$i, 'tajweedRule' => \App\Support\TajweedRules::extractFromHtml($tajweed[$key]['words'][$i] ?? null)]), JSON_UNESCAPED_UNICODE) }}">@if(isset($tajweed[$key]['words'][$i])){!! $tajweed[$key]['words'][$i] !!}@else{{ $word['arabic'] }}@endif</span>
                    @endforeach
                @elseif (isset($tajweed[$key]))
                    {!! $tajweed[$key]['html'] !!}
                @else
                    {{ $ayah['arabic'] }}
                @endif
                <x-ui.number-badge :number="$ayah['number']" class="mx-0.5 h-7 w-7" :textClass="$ayah['number'] > 99 ? 'text-[8px]' : ($ayah['number'] > 9 ? 'text-[10px]' : 'text-xs')" />
            </span>
        @endforeach
    </div>
    <footer>{{ strtr((string) $page['number'], ['0'=>'٠','1'=>'١','2'=>'٢','3'=>'٣','4'=>'٤','5'=>'٥','6'=>'٦','7'=>'٧','8'=>'٨','9'=>'٩']) }}</footer>
</div>
