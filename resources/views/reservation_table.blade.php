<x-layouts.home :title="$title" :messagetitle="$message_title" :message="$message">
  <div class="l-margintop20px" id="reservation_table">
    <table class="c-table-format">
      <thead>
        <tr>
          <th>ID</th>
          <th>予約日</th>
          <th>貸出日</th>
        </tr>
      </thead>
      <tbody>
        @foreach($reservations as $reservation)
        <tr>
          <td><a href="{{ route('home.show-reservation', ['id' => $reservation->id]) }}">{{ $reservation->id }}</a></td>
          <td>{{ $reservation->reservation_date }}</td>
          <td>{{ $reservation->borrowing_start_date }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</x-layouts.home>