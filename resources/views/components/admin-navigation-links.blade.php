@foreach($links as $link)
    @php($isActive = request()->routeIs(...$link['active']))
    <a href="{{ route($link['route']) }}"
       @class([
           'block rounded-[14px] px-4 py-3 transition-colors',
           'bg-[#2B170D] text-[#F4EBD8]' => $isActive,
           'hover:bg-[#2B170D] hover:text-[#F4EBD8]' => ! $isActive,
       ])
       @if($isActive) aria-current="page" @endif>{{ $link['label'] }}</a>
@endforeach
