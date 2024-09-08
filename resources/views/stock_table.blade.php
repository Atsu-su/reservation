<x-layouts.home :title="$title" :messagetitle="$message_title" :message="$message">
  <div class="l-margintop20px" id="stock_table">
    <p>{{ $date }}の貸出可能物品一覧</p>
    <table class="c-table-format">
      <thead>
        <tr>
          <th>#</th>
          <th>物品名</th>
          <th>貸出可能数</th>
        </tr>
      </thead>
      <tbody>
        @foreach($result as $id => $array)
        <tr>
          <td>{{ $id }}</a></td>
          <td>{{ $array['name'] }}</a></td>
          @if ($array['available_stock'] > 0)
          <td>{{ $array['available_stock'] }}</td>
          @else
          <td>貸出できません</td>
          @endif
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</x-layouts.home>