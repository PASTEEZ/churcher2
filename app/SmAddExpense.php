<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmAddExpense extends Model
{
    use HasFactory;
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
    
    public function expenseHead()
    {
        return $this->belongsTo('App\SmExpenseHead', 'expense_head_id', 'id');
    }

    public function ACHead()
    {
        return $this->belongsTo('App\SmChartOfAccount', 'expense_head_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo('App\SmBankAccount', 'account_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('App\SmPaymentMethhod', 'payment_method_id', 'id');
    }

    public function scopeAddExpense($query, $date_from, $date_to, $payment_method)
    {
        return $query->where('date', '>=', $date_from)
            ->where('date', '<=', $date_to)
            ->where('active_status', 1)
            ->where('church_id', Auth::user()->church_id)
            ->where('payment_method_id', $payment_method);
    }

}
