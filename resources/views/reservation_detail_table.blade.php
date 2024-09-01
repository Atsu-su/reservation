<x-layouts.home :title="$title" :message_title="$message_title ?? ''" :message="$message">
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
        @foreach($reservationItems as $reservationItem)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $reservationItem->item->name }}</td>
          <td>{{ $reservationItem->amount }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <button class="l-margintop20pxauto0 c-button c-button--w200px c-button--white"><a href="{{ route('home') }}">戻 る</a></button>
  </div>
</x-layouts.home>