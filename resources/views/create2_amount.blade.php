<x-layouts.home :message="$message">
  <div class="l-margintop20 " id="create1_date_items">
    <form action="" method="post">
      @csrf
      <table class="c-table-format2">
        <tr>
          <td>貸出日</td>
          <td>2024/8/9</td>
        </tr>
        <tr>
          <td><input type="hidden" value="">パソコン</td>
          <td>
            <select name="amounts[]">
              <option value="0">--- 選択してください ---</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
            </select>
          </td>
        </tr>
        <tr>
          <td><input type="hidden" value="">プリンター</td>
          <td>
            <select name="amounts[]">
              <option value="0">--- 選択してください ---</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
            </select>
          </td>
        </tr>
      </table>
      <button class="l-margintop20 button c-button" type="submit"><a>在庫確認</a></button>
    </form>
  </div>
</x-layouts.home>