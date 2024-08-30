<x-header>
  <x-slot name="title">
    {{ $title ?? '予約' }}
  </x-slot>
</x-header>
<div id="home" class="l-container">
  <div class="sidebar-container">
    <x-sidebar />
  </div>
  <main>
    <div class="message-box">
      @if (! empty($message_title))
      <h1>{{ $message_title }}</h1>
      @endif
      <p>{{ $message ?? 'メッセージはありません。' }}</p>
    </div>
    @if ($errors->any())
    <x-error />
    @endif
    {{ $slot }}
  </main>
</div>
<x-footer />