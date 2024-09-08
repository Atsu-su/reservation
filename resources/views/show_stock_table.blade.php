<x-layouts.home :title="$title" :messagetitle="$message_title" :message="$message">
  <div class="l-margintop20px" id="reservation_table">
    <table class="c-table-format">
      <thead>
        <tr>
          <th>#</th>
          <th>物品名</th>
          <th>在庫数</th>
        </tr>
      </thead>
      <tbody>
        @foreach($reservations as $reservation)
        <tr>
          <td>{{ $loop->iteration }}</a></td>
          <td><a href="{{ route('home.show-reservation', ['id' => $reservation->id]) }}">{{ $reservation->borrowing_start_date }}</a></td>
          <td>{{ $reservation->reservation_date }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</x-layouts.home>