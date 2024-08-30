<x-layouts.home :title="$title" :message_title="$message_title ?? ''" :message="$message">
  <div class="l-margintop20 id="reservation_table">
    <table class="c-table-format">
      <thead>
        <tr>
          <th>ID</th>
          <th>予約日</th>
          <th>貸出日</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($reservations as $reservation)
        <tr>
          <td>{{ $reservation->id }}</td>
          <td>{{ $reservation->reservation_date }}</td>
          <td>{{ $reservation->borrowing_start_date }}</td>
          <td><a href="{{ route('home.show_reservation', ['id' => $reservation->id]) }}">詳細へ</a></td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</x-layouts.home>