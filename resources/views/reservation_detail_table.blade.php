<x-layouts.home :title="$title" :message_title="$message_title ?? ''" :message="$message">
  <div id="reservation_detail_table">
    <table class="c-table-format">
      <thead>
        <tr>
          <th>物品名</th>
          <th>貸出個数</th>
          <th>id</th>
        </tr>
      </thead>
      <tbody>
      @foreach($reservationItems as $reservationItem)
        <tr>
          <td>{{ $reservationItem->item->name }}</td>
          <td>{{ $reservationItem->amount }}</td>
          <td>{{ $reservationItem->item->id }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</x-layouts.home>