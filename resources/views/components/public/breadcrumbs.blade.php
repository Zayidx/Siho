<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        @foreach($items as $idx => $item)
            @php($isLast = $idx === count($items) - 1)
            @if(!$isLast && !empty($item['url']))
                <li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
            @else
                <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
            @endif
        @endforeach
    </ol>
  </nav>

