<x-layouts.home :title="$title" :messagetitle="$message_title" :message="$message">
  <div class="l-margintop20px " id="select_date">
    <form action="{{ route('show-stock') }}" method="post">
      @csrf
      <table class="c-table-format2">
        <tr>
          <td>貸出日</td>
          <td><label><input type="date" value="{{ old('borrowing_start_date') }}" name="borrowing_start_date"></label></td>
        </tr>
      </table>
      <button class="l-margintop20px c-button c-button--w200px c-create-button" type="submit"><a>在庫確認</a></button>
    </form>
  </div>
</x-layouts.home>