<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\Item;
use App\Models\LendingAggregate;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    // タイトル
    private $titles = [
        'home' => ['title' => 'Home'],
        'create' => ['title' => '新規予約'],
        'update' => ['title' => '予約変更'],
        'show_stock' => ['title' => '在庫確認'],
        'show_reservation' => ['title' => '予約確認'],
    ];

    // ============================================================================
    // 検証用の設定

    // テスト用の定数
    // 仮のユーザID（ログインユーザIDとなる）
    const USER = 8;
    // 仮の予約日付
    const RESERVATION_DATE = '2024-09-04';

    // viewのモック
    public function mockView($data = null, $message = null, $collections = null)
    {
        return view('mock', [
            'data' => $data,
            'message' => $message,
            'function' => 'mockView関数からの表示です。',
            'collections' => $collections
        ]);
    }

    // リダイレクトのモック
    public function mock()
    {
        $data = session('data');
        $message = session('message');
        return view('mock', compact('data', 'message'));
    }
    // ============================================================================

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // タイトル
        $data = $this->titles['home'];

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
     * view('create1_date_items')
     */
    public function createDateItems()
    {
        // タイトル
        $data = $this->titles['create'];

        $items = Item::select('id', 'name')->get();
        $message = "貸出日と貸出物品を選択してください。";

        $data += [
            'items' => $items,
            'message' => $message
        ];

        return view('create1_date_items', $data);
    }

    /**
     * 個数を入力する画面を表示
     * return view('create2_amount')
     */
    public function createAmount(Request $request)
    {
        // タイトル
        $data = $this->titles['create'];

        // store()のバリデーションエラー発生時リダイレクト対応
        if (count(old()) > 0) {
            // 再度$resultを取得する（$date, $item_idはバリデート済み）
            $date = session('date');
            $itemIds = session('item_ids');
        } else {
            // 初めてcreateAmout()にアクセスした場合
            $inputItemIds = $request->input('item_ids');

            // item_idsから0である要素を除外
            $inputItemIds = array_filter(
                $inputItemIds,
                fn($inputItemIds) => $inputItemIds !== '0'
            );

            // 重複している値を除外
            $inputItemIds = array_unique($inputItemIds);

            // indexを0から振り直す
            $inputItemIds = array_values($inputItemIds);

            // リクエストにitem_idsの値を上書きする
            $request->merge(['item_ids' => $inputItemIds]);

            // validattion
            // ここを修正する（Validateファサードを使う）
            $validated = $request->validate([
                // 日付の制約を除く（検証用）
                'borrowing_start_date' => 'required|date',
                // 'borrowing_start_date' => 'required|date|after_or_equal:today',
                'item_ids' => 'required|array|filled',
                'item_ids.*' => 'required|integer',
            ]);

            // 変数定義
            $date = $validated['borrowing_start_date']; // 予約日
            $itemIds = $validated['item_ids'];         // 貸出物品ID
        }

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
        foreach ($itemIds as $itemId) {
            // テスト用データ
            $aggregate = LendingAggregate::diff(self::RESERVATION_DATE, $itemId);

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
                $item = Item::find($itemId);
                $result[] = [
                    'item_id' => $itemId,
                    'name' => $item->name,
                    'amount' => $item->limit,
                ];
            }
        }

        // =============================================
        // *** 作成予定 ***
        // 全ての物品が貸出不可の場合の処理
        // 戻るボタンを表示する
        // =============================================

        // セッションに保存
        session([
            'date' => $date,
            'item_ids' => $itemIds
        ]);

        $data += [
            'date' => $date,
            'result' => $result,
            'message' => '貸出数を入力してください。',
        ];

        // view
        return view('create2_amount', $data);
    }

    /**
     * Store a newly created resource in storage.
     * view('create3_result_table')
     */
    public function store(Request $request)
    {
        $data =  $this->titles['create'];

        // =============================================
        // *** 対応予定 ***
        // 戻るボタンが使われた場合のエラーはどう対処する？
        // =============================================

        // バリデーション
        $validated = $request->validate([
            'amount' => 'required|array|filled',

            // ===============================================
            // *** 作成予定 ***
            // カスタムバリデーションを追加
            // item_idsの要素一つ一つのerrorが出力されるので見栄えが悪い
            // ===============================================
            'amount.*' => 'required|integer|min:1',
        ]);

        // 変数定義
        $date = session('date');
        $itemIds = session('item_ids');
        $amount = $validated['amount'];
        $reservation = null;
        $aggregate = null;
        $out_of_stocks = [];
        $transaction = true;    // トランザクションの成否
        $items =  null;

        Log::channel('myown')->info('ログ出力開始');


        // トランザクションの開始
        DB::beginTransaction();

        try {
            // reservationsテーブルに予約情報を登録
            $reservation = Reservation::create([
                'user_id' => self::USER,
                'reservation_date' => now()->format('Y-m-d'),
                'borrowing_start_date' => $date,
            ]);

            // 以後、$reservation->idで予約IDを取得できる

            foreach ($itemIds as $index => $itemId) {
                // total_amouutにamountを加算してstockを超えないか確認
                // ※diffはstock - total_amount
                $aggregate = LendingAggregate::diff($date, $itemId);

                if ($aggregate->exists()) {
                    // 他に予約がある場合
                    $aggregate = $aggregate->lockForUpdate()->first();

                    if ($aggregate->diff - $amount[$index] >= 0) {
                        // 貸出可能
                        // lending_aggregatesテーブルのtotal_amountを更新
                        LendingAggregate::where('borrowing_start_date', $date)
                            ->where('item_id', $itemId)
                            ->update(['total_amount' => $aggregate->total_amount + $amount[$index]]);

                    } else {
                        // 貸出不可
                        $out_of_stocks += [
                            'item_id' => $itemId,
                            'name' => $aggregate->name,
                            'amount' => $amount,
                        ];

                        // foreachの次のループへ
                        continue;
                    }

                } else {
                    // 指定日・指定物品の初めての予約の場合
                    LendingAggregate::create([
                        'item_id' => $itemId,
                        'borrowing_start_date' => $date,
                        'total_amount' => $amount[$index],
                    ]);
                }

                // reservation_itemsテーブルに予約情報を登録
                ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'item_id' => $itemId,
                    'amount' => $amount[$index],
                ]);
            }

            // reservation_itemsに1件も登録できなかった場合（＝全ての物品が他の予約で貸出不可となった場合）
            if (ReservationItem::where('reservation_id', $reservation->id)->doesntExist()) {
                // throw new Exception('貸出物品の予約に失敗しました。');
            }

            // トランザクションのコミット
            DB::commit();

            // =============================================
            // *** 作業予定 ***
            // Log::infoでログを記録
            // 日付:reservation_id:登録した物品の総数（reservation_items）の形式
            // =============================================

        // 各種DB操作にエラーが発生した場合
        } catch (Exception $e) {
            // エラーをログに記録
            Log::error($e->getMessage());

            $transaction = false;

            // トランザクションのロールバック
            DB::rollBack();

        } finally {
            $message = $transaction ? '予約が完了しました。' : '予約の登録中にエラーが発生しました。（予約失敗）';

            // =============================================
            // *** 作業予定 ***
            // ReservationItemでscopeにしてもいいかも
            // show_reservation()でも使われている
            // =============================================
            $items =  ReservationItem::with('item')
                ->where('reservation_id', $reservation->id)
                ->orderBy('item_id', 'asc')
                ->get();

            $data += [
                'date' => $reservation->reservation_date,
                'items' => $items,
                'message' => $message,               // 予約成功（commit）／失敗（rollback）のメッセージ
                'transaction' => $transaction,       // transactionの成否
                'out_of_stocks' => $out_of_stocks,   // 貸出不可の物品
           ];

            // return view('show_create', $data);
            return redirect()->route('home.show_reservation_result')
                ->with(['data' => $data]);
        }
    }

    // =============================================
    // *** 作業予定 ***
    // show_reservation()と統合する
    // =============================================
    public function showReservationResult()
    {
        if (session()->has('data')){
            $data = session('data');
            return view('create3_result_table', $data);
        }
        return to_route('home');
    }

    /**
     * Display the specified resource.
     */
    public function showReservation(string $id)
    {
        // タイトル
        $data = $this->titles['show_reservation'];

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

    public function stock_show(Request $request)
    {
        // タイトル
        $data = $this->titles['show_stock'];

        // $request()->query('date')で取得

        // バリデーション（日付）

        // ReservationItem::diff()->with('item')で取得

        // dataを作成

        return view('stock_table', $data);
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
