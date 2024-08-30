<x-layouts.home :title="$title" :message_title="$message_title ?? ''" :message="$message">
  <div class="l-margintop20 " id="create2_amount">
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
            @if (! empty($item['amount']))
            <!-- 貸出可能の場合 -->
            <select name="amounts[]">
              <option value="0">--- 選択してください ---</option>
              @for ($i = 1; $i <= $item['amount']; ++$i)
              <option value="{{ $i }}">{{ $i }}</option>
              @endfor
            </select>
            @else
            <!-- 貸出不可の場合 -->
            <!-- コントローラにメッセージをのせてもいいかも -->
            <span class="not-available-message">当日の在庫がありません</span>
            @endif
          </td>
        </tr>
        @endforeach
      </table>
      <button class="l-margintop20 c-button c-button--w200px c-create-button" type="submit"><a>予約実行！</a></button>
    </form>
  </div>
</x-layouts.home>