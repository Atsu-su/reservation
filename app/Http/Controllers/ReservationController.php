<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\ReservationItem;

class ReservationController extends Controller
{

    // 仮のユーザID（ログインユーザIDとなる）
    const USER = 9;

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


        // viewに渡す
        return view('reservation_table', compact('reservations'));
    }

    /**
     * Show the form for creating a new resource.
     * 貸出物品の新規登録画面を表示
     */
    public function date_select_create()
    {
        return view('reservation_date_select');
    }

    public function item_select_create()
    {
        // ここで貸出できないものは除外する
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
