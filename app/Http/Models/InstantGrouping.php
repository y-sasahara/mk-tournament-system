<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstantGrouping extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'round',
        'result',
        'delete_reason',
    ];

    CONST FFA = 12;
    CONST TAG = 6;
    CONST TRIPLES = 4;
    CONST FOURMANCELL = 3;
    CONST TEAM = 2;

    CONST ROUND1 = 1;
    CONST ROUND2 = 2;
    CONST ROUND3 = 3;
    CONST ROUND4 = 4;
    CONST ROUND5 = 5;
    CONST ROUND6 = 6;
    CONST ROUND7 = 7;
    CONST QUARTER_FINAL = 8;
    CONST SEMI_FINAL = 9;
    CONST FINAL = 10;

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getTournamentTypeText()
    {
        switch ($this->type) {
            case self::FFA:
                return __('個人');
                break;
            case self::TAG:
                return __('タッグ');
                break;
            case self::TRIPLES:
                return __('トリプルス');
                break;
            case self::FOURMANCELL:
                return __('フォーマンセル');
                break;
            case self::TEAM:
                return __('交流戦');
                break;
            default:
                return __('対戦形式が未設定');
        }
    }

    public function getRoundText()
    {
        switch ($this->round) {
            case self::ROUND1:
                return __('1回戦');
                break;
            case self::ROUND2:
                return __('2回戦');
                break;
            case self::ROUND3:
                return __('3回戦');
                break;
            case self::ROUND4:
                return __('4回戦');
                break;
            case self::ROUND5:
                return __('5回戦');
                break;
            case self::ROUND6:
                return __('6回戦');
                break;
            case self::ROUND7:
                return __('7回戦');
                break;
            case self::QUARTER_FINAL:
                return __('準々決勝');
                break;
            case self::SEMI_FINAL:
                return __('準決勝');
                break;
            case self::FINAL:
                return __('決勝戦');
                break;
            default:
                return __('未設定');
        }
    }
}
