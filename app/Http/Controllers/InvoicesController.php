<?php

namespace App\Http\Controllers;

use App\Model\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JavaScript;

class InvoicesController extends Controller
{
    /**
     * Renders the transaction invoice.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        JavaScript::put(['phpVar' => true, 'skipDefaultScrollInits' => true]);
        $invoiceId = $request->route('id');
        $invoice = Invoice::query()->where(['id' => $invoiceId])->with('transaction')->first();

        if($invoice && $invoice->transaction && $invoice->transaction->sender_user_id !== Auth::user()->id && Auth::user()->role_id !== 1){
            abort(404);
        }

        return view('pages.invoice', ['invoice' => $invoice]);
    }
}
