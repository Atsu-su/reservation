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
use Carbon\Carbon;
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

    // メッセージタイトル
    private $messageTitles = [
        'home' => ['message_title' => 'Home'],
        'create' => ['message_title' => '新規予約'],
        'update_date' => ['message_title' => '貸出日変更'],
        'update_item' => ['message_title' => '貸出物品変更'],
        'show_stock' => ['message_title' => '在庫確認'],
        'show_reservation' => ['message_title' => '予約確認'],
    ];

    // ============================================================================
    // 検証用の設定

    // テスト用の定数
    // 仮のユーザID（ログインユーザIDとなる）
    const USER = 1;
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

    public function mock2()
    {
        $date = session('date');
        $message = session('message');
        return view('mock', compact('date', 'message'));
    }
    // ============================================================================

    /**
     * 予約日が重複しているかチェック
     * 重複している場合はリダイレクト
     * @param array $value
     * @return \Illuminate\Http\RedirectResponse | void
     * $value = [
     *  'user_id' => [int],
     *  'borrowing_start_date' => [string],
     *  'redirect' => [string],
     *  'message' => [string],
     * ]
     */
    public function doubleBookedCheck(array $value)
    {
        // 予約日とユーザIDで検索してreservationsテーブルを検索し、
        // レコードがあれば更新処理へ転送
        if (Reservation::where('user_id', $value['user_id'])
            ->where('borrowing_start_date', $value['borrowing_start_date'])
            ->exists()
        ) {
            return redirect()->route($value['redirect'])->with([
                'date' => $value['borrowing_start_date'],
                'message' => $value['message'],
            ]);
        }
    }

    /**
     * 貸出可能な物品数を出力する
     * @param string $date, array $itemIds, array $result
     * $resultは参照渡し（ポインタ）とする
     */
    // public function setAmount($date, $itemIds, &$result)
    // 開発用関数
    public function setAmount($date = self::RESERVATION_DATE, $itemIds, &$result)
    {
        foreach ($itemIds as $itemId) {
            // テスト用データ
            $aggregate = LendingAggregate::diff($date, $itemId);

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
    }

    public function dbOperationCU(
        string $date,
        array $itemIds,
        array $amount,
        &$out_of_stocks,
        $reservation_id = null,
        $operation = 'create')
    {
        // create・updateの判定
        if ($operation === 'create') {
            // reservationsテーブルに予約情報を登録
            $reservation = Reservation::create([
                'user_id' => self::USER,
                'reservation_date' => now()->format('Y-m-d'),
                'borrowing_start_date' => $date,
            ]);

        } else {
            $reservation = Reservation::find('reservation_id');
        }

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

            // create・updateの判定
            if ($operation === 'create') {
                // reservation_itemsテーブルに予約情報を登録
                ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'item_id' => $itemId,
                    'amount' => $amount[$index],
                ]);
            } else {
                // update
            }
        }

        // reservation_itemsに1件も登録できなかった場合（＝全ての物品が他の予約で貸出不可となった場合）
        if (ReservationItem::where('reservation_id', $reservation->id)->doesntExist()) {
            throw new Exception('貸出物品の予約に失敗しました。');
        }
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
            ...$this->titles['home'],
            ...$this->messageTitles['home'],
            'reservations' => $reservations,
            'message' => $message
        ];

        // viewに渡す
        return view('reservation_table', $data);
    }

    public function selectDate()
    {
        $data = [
            ...$this->titles['show_stock'],
            ...$this->messageTitles['show_stock'],
            'message' =>  '在庫を確認したい日付を選択してください。',
        ];
        return view('select_date', $data);
    }

    public function showStock(Request $request)
    {
        // バリデーション（日付）
        $validated = $request->validate([
            'borrowing_start_date' => 'required|date',
        ]);

        $date =  $validated['borrowing_start_date'];

        $stocks = LendingAggregate::with('item')
            ->where('borrowing_start_date', $date)
            ->orderBy('item_id', 'asc')
            ->get();

        // データを整形する（1 => [name => A, total_amount => x], ...)
        $itemCount = Item::count();
        $result = [];
        for ($i = 1; $i <= $itemCount; $i++) {
            $stock = $stocks->firstWhere('item_id', $i);
            $item =  Item::find($i);
            if ($stock) {
                $result[$i] = [
                    'name' =>  $stock->item->name,
                    'available_stock' => ($item->stock_amount - $stock->total_amount),
                ];
            } else {
                $result[$i] = [
                    'name' =>  $item->name,
                    'available_stock' => $item->stock_amount,
                ];
            }
        }

        // 情報取得時間を取得
        $now =  Carbon::now();
        $formattedNow = $now->format('Y年n月j日 G時i分s秒');

        // dataを作成
        $data = [
            ...$this->titles['show_stock'],
            ...$this->messageTitles['show_stock'],
            'message' => "{$formattedNow}時点の在庫状況です。",
            'date' => $date,
            'result' =>  $result,
        ];

        return view('stock_table', $data);
    }

    /**
     * Show the form for creating a new resource.
     * 貸出物品の新規登録画面を表示
     * view('create1_date_items')
     */
    public function createDateItems()
    {
        $items = Item::select('id', 'name')->get();
        $message = "貸出日と貸出物品を選択してください。";

        $data = [
            ...$this->titles['create'],
            ...$this->messageTitles['create'],
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
        // store()のバリデーションエラー発生時リダイレクト対応
        if (count(old()) > 0) {
            // 再度$resultを取得する（$date, $item_idはバリデート済み）
            $date = session('create_date');
            $itemIds = session('create_item_ids');
        } else {
            // 初めてcreateAmount()にアクセスした場合
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

        // セッション初期化
        session()->forget(['create_date', 'create_item_ids']);

        // 変数定義
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
            ->exists()
        ) {
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
            'create_date' => $date,
            'create_item_ids' => $itemIds
        ]);

        $data = [
            ...$this->titles['create'],
            ...$this->messageTitles['create'],
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
        $date = session('create_date');
        $itemIds = session('create_item_ids');
        $amount = $validated['amount'];
        $reservation = null;
        $aggregate = null;
        $out_of_stocks = [];
        $transaction = true;    // トランザクションの成否
        $items =  null;

        // トランザクションの開始
        Log::channel('myown')->info('[CREATE]トランザクション開始');
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

                        // =============================================
                        // この変数をbladeで扱う際にエラーが出力される
                        // [previous exception] [object] (TypeError(code: 0): Cannot access offset of type string on string at /Applications/MAMP/htdocs/work/laravel/reservation/storage/framework/views/baed35d73a60ea5d856f98e7e7248368.php:56)
                        // =============================================
                        $out_of_stocks[] = [
                            'item_id' => $itemId,
                            'name' => $aggregate->name,
                            'amount' => $amount[$index],
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
                throw new Exception('貸出物品の予約に失敗しました。');
            }

            // トランザクションのコミット
            DB::commit();
            Log::channel('myown')->info('[CREATE]トランザクション完了');

            // セッションの削除（finallyにおくと戻った場合に再度処理ができない...できなくていいのかもだが）
            session()->forget(['create_date', 'create_item_ids']);

            // =============================================
            // *** 作業予定 ***
            // Log::infoでログを記録
            // 日付:reservation_id:登録した物品の総数（reservation_items）の形式
            // =============================================

        // 各種DB操作にエラーが発生した場合
        } catch (Exception $e) {
            // エラーをログに記録
            Log::error('An error occurred: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $transaction = false;

            // トランザクションのロールバック
            DB::rollBack();
            Log::channel('myown')->error('[CREATE]トランザクションロールバック');

        } finally {
            $message = $transaction ? '予約が完了しました。' : '予約の登録中にエラーが発生しました。（予約失敗）';

            // セッションの削除
            session()->forget(['create_date', 'create_item_ids']);

            // =============================================
            // *** 作業予定 ***
            // ReservationItemでscopeにしてもいいかも
            // show_reservation()でも使われている
            // =============================================
            if ($reservation) {
                $items =  ReservationItem::with('item')
                    ->where('reservation_id', $reservation->id)
                    ->orderBy('item_id', 'asc')
                    ->get();
            }

            $data = [
                ...$this->titles['create'],
                ...$this->messageTitles['create'],
                'date' => $date,                     // 貸出日
                'items' => $items,
                'message' => $message,               // 予約成功（commit）／失敗（rollback）のメッセージ
                'transaction' => $transaction,       // transactionの成否
                'out_of_stocks' => $out_of_stocks,   // 貸出不可の物品
            ];

            dd($data);

            // return view('show_create', $data);
            return redirect()->route('home.show-reservation-result')
                ->with(['data' => $data]);
        }
    }

    // =============================================
    // *** 作業予定 ***
    // show_reservation()と統合する
    // =============================================
    public function showReservationResult()
    {
        if (session()->has('data')) {
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
        session()->forget('reservation_id');

        // タイトル
        $data = $this->titles['show_reservation'];

        // 貸出物品の詳細を表示
        $borrowingStartDate = Reservation::where('id', $id)->value('borrowing_start_date');
        $items = ReservationItem::with('item')
            ->where('reservation_id', $id)
            ->orderBy('item_id', 'asc')
            ->get();

        // message
        $message = "貸出日は{$borrowingStartDate}です。";

        session(['reservation_id' => $id]);

        $data += [
            'items' => $items,
            'message_title' => '予約詳細',
            'message' => $message,
        ];

        return view('reservation_detail_table', $data);
    }

    /**
     * 貸出日選択画面の表示（貸出日の変更）
     * return view('edit1_date')
     */
    public function editDate(string $id)
    {
        session()->forget(
            'edit_items_ids',
            'edit_items',
            'edit_item_names',
            'edit_old_item_amount'
        );

        // reservation_idがこのメソッドに渡される
        // 日付の変更／貸出物品の変更の2種類

        // 予約済みの物品情報を取得
        $items = ReservationItem::with('item')
            ->where('reservation_id', $id)
            ->orderBy('reservation_items.item_id', 'asc')
            ->get();

        $itemIds = $items->pluck('item_id')->toArray();
        $itemNames = $items->pluck('item.name')->toArray();
        $oldItemAmount = $items->pluck('amount')->toArray();

        // 貸出予定の物品ID／物品名／貸出数をセッションに保存
        session([
            'reservation_id' =>  $id,
            'edit_item_ids' => $itemIds,
            'edit_item_names' =>  $itemNames,
            'edit_old_item_amount' => $oldItemAmount,
        ]);

        // $dataを準備
        $data = [
            ...$this->titles['update'],
            'reservation_id' => $id,
            'item_names' => $itemNames,
            'message_title' => '予約更新（貸出日変更）',
            'message' => '予約日を変更してください。',
        ];

        // 予約日の変更画面を表示
        return view('edit_date', $data);
    }

    public function editAmount(Request $request)
    {
        if (count(old()) > 0) {
            $date = session('edit_date');

        } else {
            // バリデーション
            $validated =  $request->validate([
                'borrowing_start_date' => 'required|date',
            ]);

            // 変数定義
            $date =  $validated['borrowing_start_date'];
            $itemIds = session('edit_item_ids');
            // $userId =  auth()->id();
        }

        // セッション初期化
        session()->forget(['edit_date']);

        // ---------------------------------------------------------
        // 指定された日付で既に予約されている場合、更新処理へリダイレクト
        // ---------------------------------------------------------
        $value = [
            'user_id' => self::USER,
            // 'user_id' => $userId,
            'borrowing_start_date' => $date,
            'redirect' => 'mock2',
            'message' => '予約日が重複しています。更新処理を行いますか？',
        ];

        $this->doubleBookedCheck($value);

        // ---------------------------------------------------------
        // 指定された日付に予約がない場合、貸出可能数を決定する
        // ---------------------------------------------------------
        $result = [];

        $this->setAmount($date, $itemIds, $result);

        // セッションに保存
        session([
            'edit_date' => $date,
        ]);

        $data = [
            ...$this->titles['update'],
            ...$this->messageTitles['update_date'],
            'date' => $date,
            'result' => $result,
            'message_title' =>  '予約更新（貸出日変更）',
            'message' => '貸出数を入力してください。',
        ];

        // create2_amount -> select_amountへ変更予定
        return view('create2_amount', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        dd(session()->all());
        // 貸出数のバリデーション

        // 1. 日付変更の場合
        // （変更箇所）
        // ・lending_aggregatesテーブルのtotal_amountを更新（date/item_id/amount）
        //   -> 既に予約されている物品のキャンセル（total_amountを減らす）※割込関連のエラーはなし
        //   -> 変更された日のtotal_amountを増やす ※割込関連のエラーあり
        // ・reservation_itemsテーブルのamountを更新（reservation_id/item_id/amount）
        // ・reservationテーブルのborrowing_start_dateを更新（reservation_id）

        return <<<EOF
        成功！
EOF;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
