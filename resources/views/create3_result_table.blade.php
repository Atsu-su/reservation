<x-layouts.home :title="$title" :message_title="$message_title ?? ''" :message="$message">
  <div id="create3_result_table">
    <div class="reservation-failure l-margintop20px">
      <div class="reservation-failure-message">
        <p>時間をおいて再度予約してください。</p>
      </div>
    </div>
    <div class="success-table">
      <h2 class="l-margintop20px">予約日：2024-12-12</h2>
      <table class="c-table-format">
        <thead>
          <tr>
            <th>#</th>
            <th>物品名</th>
            <th>貸出個数</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>name</td>
            <td>amount</td>
          </tr>
          <tr>
            <td>2</td>
            <td>name</td>
            <td>amount</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="failure-table">
      <p class="l-margintop20px">次の物品の予約に失敗しました。</p>
      <p><a href="">こちらの予約変更</a>から再度予約してください。</p>
      <table class="c-table-format c-table-format--background-red">
        <thead>
          <tr>
            <th>#</th>
            <th>物品名</th>
            <th>貸出個数</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>name</td>
            <td>amount</td>
          </tr>
          <tr>
            <td>2</td>
            <td>name</td>
            <td>amount</td>
          </tr>
        </tbody>
      </table>
    </div>
    <button class="l-margintop20pxauto0 c-button c-button--w200px c-button--white"><a href="">ホームに戻る</a></button>

    <!-- ==================================================== -->
    {{-- <h2>予約日:{{ $date }}</h2> --}}

    {{-- @foreach($items as $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
    <td>{{ $item->item->name }}</td>
    <td>{{ $item->amount }}</td>
    </tr>
    @endforeach
    </tbody>
    </table>
    @if (! empty($out_of_stocks))
    <p>次の物品の予約に失敗しました。</p>
    <table class="c-table-format">
      <thead>
        <tr>
          <th>#</th>
          <th>物品名</th>
          <th>貸出個数</th>
        </tr>
      </thead>
      <tbody>
        @foreach($out_of_stocks as $out_of_stock)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $out_of_stock['name'] }}</td>
          <td>{{ $out_of_stock['amount'] }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
    <button class="l-margintop20px c-button c-button--w200px c-button--white"><a href="{{ route('home') }}">戻 る</a></button>
    --}}
  </div>
</x-layouts.home>