<x-header>
  <x-slot name="title">
    Home
  </x-slot>
</x-header>
<div id="home" class="l-container">
  <div class="sidebar-container">
    <x-sidebar />
  </div>
  <main>
    <div class="message-box">
      {{ $message ?? 'メッセージはありません。' }}
    </div>
    @if ($errors->any())
    <x-error />
    @endif
    {{ $slot }}
  </main>
</div>
<x-footer />