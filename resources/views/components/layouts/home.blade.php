<x-header>
  <x-slot name="title">
    Home
  </x-slot>
</x-header>
<x-action-button />
<div id="home" class="l-container">
    <div class="side-bar">
      <ul>
        <li>マイプロフィール</li>
        <li>貸出物品</li>
        <li>予約ルール</li>
      </ul>
    </div>
    <div class="reservation-table">
      {{ $slot }}
    </div>
</div>
<x-footer />