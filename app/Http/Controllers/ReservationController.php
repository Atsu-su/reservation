<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\LendingAggregate;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\User;

class ReservationController extends Controller
{
    // タイトル
    private $title = [
        'home' => ['title' => 'Home'],
        'create' => ['title' => '新規予約'],
        'update' => ['title' => '予約変更'],
        'show_stock' => ['title' => '在庫確認'],
        'show_reservation' => ['title' => '予約確認'],
    ];

    // -------------------------------------
    // テスト用の定数
    // 仮のユーザID（ログインユーザIDとなる）
    const USER = 9;
    // 仮の予約日付
    const RESERVATION_DATE = '2024-09-02';
    // -------------------------------------

    public function mockView($data = null, $message = null, $collections = null)
    {
        return view('mock', [
            'data' => $data,
            'message' => $message,
            'function' => 'mockView関数からの表示です。',
            'collections' => $collections
        ]);
    }

    // リダイレクト用のモック
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
        // タイトル
        $data = $this->title['home'];

        // 貸出情報（reservationsテーブルのid/borrowing_start_date/reservation_date）
        $reservations = Reservation::where('user_id', self::USER)
            ->orderBy('reservation_date', 'asc')
            ->orderBy('borrowing_start_date', 'asc')
            ->get(['id', 'reservation_date', 'borrowing_start_date']);

        $user = User::find(self::USER);
        $message = "ID番号:{$user->id} {$user->name}さんの貸出予約情報です。";

        $data += [
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
        // タイトル
        $data = $this->title['create'];

        $items = Item::select('id', 'name')->get();
        $message = "貸出日と貸出物品を選択してください。";

        $data += [
            'items' => $items,
            'message' => $message
        ];

        return view('create1_date_items', $data);
    }

    public function create2_amount(Request $request)
    {
        // タイトル
        $data = $this->title['create'];

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
        $validated = $request->validate([
            // 日付の制約を除く（検証用）
            'borrowing_start_date' => 'required|date',
            // 'borrowing_start_date' => 'required|date|after_or_equal:today',
            'item_ids' => 'required|array|filled',
            'item_ids.*' => 'required|integer',
        ]);

        // 変数定義
        $date = $validated['borrowing_start_date']; // 予約日
        $item_ids = $validated['item_ids'];         // 貸出物品ID
        $result = [];       // viewに渡す配列
        $aggregate = null;  // 集計テーブルのクエリ
        $record = null;     // 集計テーブルのレコード
        $item = null;       // 集計テーブルにまだ存在していない物品（その日はまだ予約が入っていない物品）

        // ---------------------------------------------------------
        // 指定された日付で既に予約されている場合、更新処理へリダイレクト
        // ---------------------------------------------------------

        // 貸出日とユーザIDで検索してreservationsテーブルを検索し、
        // レコードがあれば更新処理へ転送
        if (Reservation::where('user_id', self::USER)
            ->where('borrowing_start_date', $date)
            ->exists()) {
            return redirect()->route('mock')->with([
                'data' => $date,
                'message' => '予約日が重複しているのでリダイレクトされました。'
            ]);
        }

        // ---------------------------------------------------------
        // 指定された日付に予約がない場合、貸出可能数を決定する
        // ---------------------------------------------------------

        // 他で使う場合はメソッドにする
        foreach ($item_ids as $item_id) {
            // テスト用データ
            $aggregate = LendingAggregate::diff(self::RESERVATION_DATE, $item_id);
            // $aggregate = LendingAggregate::diff($date, $item_id)->get();
            if ($aggregate->exists()) {
                $record = $aggregate->first();
                if ($record->diff > 0) {
                     // 貸出可能なので、貸出数を決定する
                     $amount = $record->diff < $record->limit ? $record->diff : $record->limit;
                     $result[] = [
                        'item_id' => $record->item_id,
                        'name' => $record->name,
                        'amount' => $amount,
                    ];
                } else {
                    // 貸出不可
                    $result[] = [
                        'item_id' => $record->item_id,
                        'name' => $record->name,
                        'amount' => 0,
                     ];
                }
            } else {
                // 貸出可能
                $item = Item::find($item_id);
                $result[] = [
                    'item_id' => $item_id,
                    'name' => $item->name,
                    'amount' => $item->limit,
                ];
            }
        }

        $data =[
            'date' => $date,
            'result' => $result,
            'message' => '貸出数を入力してください。',
        ];

        // view
        return view('create2_amount', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // バリデーション

        // トランザクションの開始

        // try {
        //
        // reservationsテーブルに予約情報を登録
        // $reservation = Reservation::create([...]);
        //
        // 以後、$reservation->idで予約IDを取得できる
        //
        // foreach (item_ids as item_id)
        //     total_amouutにamountを加算してstockを超えないか確認
        //
        //     ※diffはstock - total_amount
        //
        //     ここで占有ロックをかける
        //     $diff = LendingAggregate::diff($date, $item_id)
        //         ->lockForUpdate()
        //         ->first();
        //
        //     if ($diff - $amouut > 0)
        //         貸出可能
        //         lending_aggregatesテーブルのtotal_amountを更新
        //         if (更新失敗)
        //             throw new Exception('貸出物品の予約に失敗しました。');
        //         reservation_itemsテーブルに予約情報を登録
        //         if (登録失敗)
        //             throw new Exception('貸出物品の予約に失敗しました。');
        //     else
        //         貸出不可
        //         $error_items[] = [
        //             'item_id' => $item_id,
        //             'name' => Item::find($item_id)->name,
        //             'amount' => $amount,
        //         ];
        //
        // if (reservation_itemsに1件も登録できなかった場合)
        //      throw new Exception('貸出物品の予約に失敗しました。');
        //
        // トランザクションのコミット
        //
        // } catch (Exception $e) {
        //    エラーをログに記録
        //    \Log::error('予約登録エラー: ' . $e->getMessage());
        //
        //    ユーザーフレンドリーなメッセージを準備
        //    $errorMessage = '予約の登録中にエラーが発生しました。後でもう一度お試しください。';
        //
        //    エラーメッセージをフラッシュデータとしてセッションに保存
        //    session()->flash('error', $errorMessage);
        //
        //    エラーページや元のページにリダイレクト
        //    return redirect()->back()->withInput();
        // }
        //
        // if (empty(error_items))
        //     成功時の処理
        //     session()->flash('success', '予約が正常に登録されました。');
        //     return redirect()->route('reservations.show', $reservation->id);
        // else
        //     $data = [
        //        'id' => reservation->id,
        //        'date' => $reservation->reservation_date,
        //        'error' => $error_items,..
        //    ];
        //    return view('show', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show_reservation(string $id)
    {
        // タイトル
        $data = $this->title['show_reservation'];

        // 貸出物品の詳細を表示
        $reservationDate = Reservation::where('id', $id)->value('reservation_date');
        $reservationItems = ReservationItem::with('item')
            ->where('reservation_id', $id)
            ->get()
            ->sortBy('item.id');

        // message
        $message = "貸出日は{$reservationDate}です。";

        $data += [
            'message' => $message,
            'reservationItems' => $reservationItems
        ];

        return view('reservation_detail_table', $data);
    }

    public function stock_show()
    {
        //
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
