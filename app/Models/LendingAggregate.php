<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LendingAggregate extends Model
{
    use HasFactory;

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * 貸出集計情報を取得
     * @param $query
     * @param $date     貸出日を想定
     * @param $id       選択されたidを想定
     * @return mixed
     */
    public function scopeDiff($query, $date, $id)
    {
        return $query->from('lending_aggregates as l')
            ->where('l.borrowing_start_date', $date)
            ->where('l.item_id', $id)
            ->join('items as i', 'l.item_id', '=', 'i.id')
            ->select(
                'l.item_id',
                'i.name',
                'l.total_amount',
                'i.stock_amount',
                DB::raw('CAST(i.stock_amount AS SIGNED) - CAST(l.total_amount AS SIGNED) as diff'),
                'limit'
            );
    }
}