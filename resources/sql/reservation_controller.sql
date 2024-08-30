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