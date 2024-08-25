<x-layouts.home :message="$message">
  <div class="l-margintop20 " id="create1_date_items">
    <form action="{{ route('home.create2') }}" method="post">
      @csrf
      <table class="c-table-format2">
        <tr>
          <td>貸出日</td>
          <td><label><input type="date" name="borrowing_start_date"></label></td>
        </tr>
        @for ($i = 0; $i < 5; $i++)
        <tr>
          <td>物品{{ $i + 1 }}</td>
          <td>
            <select name="item_ids[]">
              <option value="0">--- 選択してください ---</option>
              @foreach ($items as $item)
              <option value="{{ $item->id }}" {{ $item->id == old("item_ids.{$i}") ? 'selected' : '' }}>
                {{ $item->name }}
              </option>
              @endforeach
            </select>
          </td>
        </tr>
        @endfor
      </table>
      <button class="l-margintop20 button c-button" type="submit"><a>在庫確認</a></button>
    </form>
  </div>
</x-layouts.home>