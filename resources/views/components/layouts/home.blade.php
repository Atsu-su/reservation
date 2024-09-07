<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? '予約' }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
  <x-header />
  <div id="home" class="l-container">
    <div class="sidebar-container">
      <x-sidebar />
    </div>
    <main>
      <div class="message-box">
        @if (! empty($messagetitle))
          <h1>{{ $messagetitle }}</h1>
        @endif
        <p>{{ $message ?? 'メッセージはありません。' }}</p>
      </div>
      <!-- バリデーションエラー用 -->
      @if ($errors->any())
        <x-validation_error />
      @endif
      {{ $slot }}
    </main>
  </div>
  <x-footer />
</body>
</html>