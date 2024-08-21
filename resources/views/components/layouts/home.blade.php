<x-header>
  <x-slot name="title">
    Home
  </x-slot>
</x-header>
<div id="home" class="l-container">
  <div class="home-container">
    <x-sidebar />
    <main>
      <div class="message-box">
        {{ $message ?? 'メッセージはありません。' }}
      </div>
      {{ $slot }}
    </main>
  </div>
</div>
<x-footer />