<x-layouts.home :title="$title" :messagetitle="$message_title" :message="$message">
  <div id="reservation_detail_table">
    <table class="c-table-format">
      <thead>
        <tr>
          <th>#</th>
          <th>物品名</th>
          <th>貸出個数</th>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $item->item->name }}</td>
          <td>{{ $item->amount }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <!-- 更新用ボタンを並べる -->
    <div class="update-buttons">
      <button class="l-margintop20pxauto0 c-button c-button--w200px c-button--white">
        <a href="{{ route('home.edit-date', session('reservation_id')) }}">貸出日変更</a>
      </button>
      <button class="l-margintop20pxauto0 c-button c-button--w200px c-button--white">
        <a href="">貸出物品変更</a>
      </button>
    </div>
    <button class="l-margintop20pxauto0 c-button c-button--w200px c-button--white">
      <a href="{{ route('home') }}">戻 る</a>
    </button>
  </div>
</x-layouts.home>