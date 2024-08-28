<x-layouts.home :message="$message ?? 'メッセージはありません。'">
<p>{{ $function ?? '' }}</p>
<h2 style="margin: 40px; font-size: 36px; font-weight: bold;">表示成功！</h2>
  <p style="margin: 40px; font-size: 36px">{{ $data ?? 'データなし' }}</p>
  @if (! empty($collections))
  <ul>
    @foreach ($collections as $collection)
    <li>{{ json_encode($collection, JSON_UNESCAPED_UNICODE) }}</li>
    @endforeach
  </ul>
  @endif
</x-layouts.home>