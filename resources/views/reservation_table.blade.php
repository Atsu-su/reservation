<x-layouts.home>
  <div id="reservation_table">
      <table>
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
            <td>{{ $reservation->id }}</td>
            <td>{{ $reservation->reservation_date }}</td>
            <td>{{ $reservation->borrowing_start_date }}</td>
          </tr>
        @endforeach
      </table>
  </div>
</x-layouts.home>