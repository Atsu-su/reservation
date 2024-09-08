<x-layouts.home :title="$title" :messagetitle="$message_title" :message="$message">
  <div class="l-margintop20px " id="create2_amount">
    @if (Str::contains(request()->url(), 'create'))
    <form action="{{ route('home.store') }}" method="post">
    @else
    <form action="{{ route('home.update') }}" method="post">
    @endif
    @csrf
      <table class="c-table-format2">
        <tr>
          <td>貸出日</td>
          <td>{{ $date }}</td>
        </tr>
        @foreach ($result as $index => $item)
        <tr>
          <td>{{ $item['name'] }}</td>
          <td>
            @if (! empty($item['amount']))
            <!-- 貸出可能の場合 -->
            <select name="amount[]">
              <option>--- 選択してください ---</option>
              @for ($i = 1; $i <= $item['amount']; ++$i)
                <option value="{{ $i }}" {{ $i == old("amount.{$index}") ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
            @else
            <!-- 貸出不可の場合 -->
            <!-- ============================
                 *** 作業予定 ***
                 スタイルをつける
                 ============================ -->
            <span class="not-available-message">当日の在庫がありません</span>
            @endif
          </td>
        </tr>
        @endforeach
      </table>
      <button class="l-margintop20px c-button c-button--w200px c-create-button" type="submit"><a>予約実行！</a></button>
    </form>
  </div>
</x-layouts.home>