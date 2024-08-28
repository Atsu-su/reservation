<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\User;

class ReservationController extends Controller
{

    // 仮のユーザID（ログインユーザIDとなる）
    const USER = 9;
    // 仮の予約日付
    const RESERVATION_DATE = '2024-08-31';

    public function mockView($data = null, $message = null, $collections = null)
    {
        return view('mock', [
            'data' => $data,
            'message' => $message,
            'function' => 'mockView関数からの表示です。',
            'collections' => $collections
        ]);
    }

    public function mock()
    {
        $data = session('data');
        $message = session('message');
        return view('mock', compact('data', 'message'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 貸出情報（reservationsテーブルのid/borrowing_start_date/reservation_date）
        $reservations = Reservation::where('user_id', self::USER)
            ->orderBy('reservation_date', 'asc')
            ->orderBy('borrowing_start_date', 'asc')
            ->get(['id', 'reservation_date', 'borrowing_start_date']);

        $user = User::find(self::USER);
        $message = "ID番号:{$user->id} {$user->name}さんの貸出予約情報です。";

        $data = [
            'reservations' => $reservations,
            'message' => $message
        ];

        // viewに渡す
        return view('reservation_table', $data);
    }

    /**
     * Show the form for creating a new resource.
     * 貸出物品の新規登録画面を表示
     */
    public function create1_date_items()
    {
        $items = Item::select('id', 'name')->get();
        $message = "貸出日と貸出物品を選択してください。";

        $data = [
            'items' => $items,
            'message' => $message
        ];

        return view('create1_date_items', $data);
    }

    public function create2_amount(Request $request)
    {

        // item_idsから0である要素を除外
        $item_ids = array_filter(
            $request->input('item_ids'),
            fn($item_id) => $item_id !== '0'
        );

        // 重複している値を除外
        $item_ids = array_unique($item_ids);

        // indexを0から振り直す
        $item_ids = array_values($item_ids);

        // リクエストにitem_idsの値を上書きする
        $request->merge(['item_ids' => $item_ids]);

        // validattion
        $validateData = $request->validate([            
            // 日付の制約を除く（検証用）
            'borrowing_start_date' => 'required|date',
            // 'borrowing_start_date' => 'required|date|after_or_equal:today',
            'item_ids' => 'required|array|filled',
            'item_ids.*' => 'required|integer',
        ]);

        // 貸出日とユーザIDで検索してreservationsテーブルを検索し、
        // レコードがあれば更新処理へ転送
        if (Reservation::where('user_id', self::USER)
            ->where('borrowing_start_date', $validateData['borrowing_start_date'])
            ->exists()) {
            return redirect()->route('mock')->with([
                'data' => $validateData['borrowing_start_date'],
                'message' => '予約日が重複しているのでリダイレクトされました。'
            ]);
        }

        // 貸出日当日の貸出予定数を取得
        $reservedItems = DB::table('reservation_items as ri')
            ->join('reservations as r', 'ri.reservation_id', '=', 'r.id')
            ->join('items as i', 'ri.item_id', '=', 'i.id')
            // 仮の日付を使う
            ->where('r.borrowing_start_date', self::RESERVATION_DATE)
            // ->where('reservations.borrowing_start_date', $validateData['borrowing_start_date'])
            ->groupBy('ri.item_id')
            ->whereIn('ri.item_id', $validateData['item_ids'])
            ->select('ri.item_id', 'i.name', DB::raw("sum(ri.amount) as total_amount"))
            ->orderBy('ri.item_id')
            ->get();

        // inventoriesテーブルをitemsテーブルに統合する

        return $this->mockView(
            collections: $reservedItems);

        // viewに渡すデータを定義
        $amountArray = [];

        // 在庫数との比較で貸出可否を決定
        // foreach ($reservedItems as $reservedItem)
        //     $diff = 在庫数 - 貸出予定数の和
        //     if (在庫数 - 貸出予定数の和) > 0
        //         貸出可能
        //         貸出数を決定する
        //             $amount = 在庫数 - 貸出予定数の和 < limit ? 在庫数 - 貸出予定数の和 : limit
        //         $amountArrayにデータを追加
        //     else
        //         貸出不可
        //         $amountArrayにデータを追加

        // view
        // return view('create2_amount', $data);
        return view('mock', [
            'data' => 'dataなし',
            'message' => 'create2_amount正常終了'
        ]);

        /*
        （参考）
        select item_id, i.name, total_amount from
            (select ri.item_id, sum(ri.amount) as total_amount
            from reservation_items ri
            inner join reservations r on ri.reservation_id = r.id
            where r.borrowing_start_date = '2024-08-28' group by ri.item_id order by 1) tmp
        inner join items i on tmp.item_id = i.id;

        完全版のSQL
        select
            tmp2.item_id,
            tmp2.name,
            iv.stock_amount,
            tmp2.total_amount,
            stock_amount - total_amount as remaining_amount
        from
            (select item_id, i.name, total_amount from
                (select ri.item_id, sum(ri.amount) as total_amount
                from reservation_items ri
                inner join reservations r on ri.reservation_id = r.id
                where r.borrowing_start_date = '2024-08-31' group by ri.item_id order by 1) tmp
            inner join items i on tmp.item_id = i.id) tmp2
        left outer join inventories iv on tmp2.item_id = iv.item_id;

        括弧の中のSQLの参考となるもの
        $groups = DB::table('groups')  //＄groupsは変数にいれてるだけ
           ->leftJoin('users', 'groups.id', '=', 'users.group_id')
           ->select('groups.id', 'groups.name', DB::raw("count(users.group_id) as count"))
           ->groupBy('groups.id', 'gorups.name')
           ->get();
        */
    }

    public function amount_select_create()
    {
        /*
        select item_id, i.name, total_amount from
            (select ri.item_id, sum(ri.amount) as total_amount
            from reservation_items ri
            inner join reservations r on ri.reservation_id = r.id
            where r.borrowing_start_date = '2024-08-26' group by ri.item_id order by 1) tmp
        inner join items i on tmp.item_id = i.id;

        括弧の中のSQLの参考となるもの
        $groups = DB::table('groups')  //＄groupsは変数にいれてるだけ
           ->leftJoin('users', 'groups.id', '=', 'users.group_id')
           ->select('groups.id', 'groups.name', DB::raw("count(users.group_id) as count"))
           ->groupBy('groups.id', 'gorups.name')
           ->get();
        */
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // 貸出物品の詳細を表示
        $reservationDate = Reservation::where('id', $id)->value('reservation_date');
        $reservationItems = ReservationItem::with('item')
            ->where('reservation_id', $id)
            ->get()
            ->sortBy('item.id');

        // message
        $message = "貸出日は{$reservationDate}です。";

        $data = [
            'message' => $message,
            'reservationItems' => $reservationItems
        ];

        return view('reservation_detail_table', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
