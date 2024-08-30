<x-layouts.home :message="$message">
  <div class="l-margintop20 " id="create1_date_items">
    <form action="" method="post">
      @csrf
      <table class="c-table-format2">
        <tr>
          <td>貸出日</td>
          <td>{{ $date }}</td>
        </tr>
        @foreach ($result as $item)
        <tr>
          <td><input type="hidden" value="{{ $item['item_id'] }}">{{ $item['name'] }}</td>
          <td>
            <select name="amounts[]">
              <option value="0">--- 選択してください ---</option>
              @for ($i = 1; $i <= $item['amount']; ++$i)
              <option value="{{ $i }}">{{ $i }}</option>
              @endfor
            </select>
          </td>
        </tr>
        @endforeach
      </table>
      <button class="l-margintop20 button c-button" type="submit"><a>予約実行！</a></button>
    </form>
  </div>
</x-layouts.home>