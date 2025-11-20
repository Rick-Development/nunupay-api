<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CountryCurrency;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VirtualCardOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TxnSummaryController extends Controller
{
    public function index()
    {
        $statistics['schedule'] = $this->dayList();
        $data['currencies'] = CountryCurrency::whereRelation('country', 'send_to', '=', 1)
            ->orWhereRelation('country', 'receive_from', '=', 1)
            ->orderByUserWalletBalance()
            ->get();
        return view('admin.txn_summary.index', $data, compact("statistics"));
    }

    public function details($code)
    {
        $statistics['schedule'] = $this->dayList();
        return view('admin.txn_summary.details', compact("statistics",'code'));
    }

    public function monthlyTransaction(Request $request, $code = null)
    {
        $keyDataset = $request->keyDataset;
        $dailyTransaction = $this->dayList();
        $dailyProfit = $this->dayList();

        Transaction::when($keyDataset == '0', function ($query) use ($code) {
            $query->whereMonth('created_at', Carbon::now()->month);
            if ($code){
                $query->where('currency', $code);
            }
        })
            ->when($keyDataset == '1', function ($query) {
                $lastMonth = Carbon::now()->subMonth();
                $query->whereMonth('created_at', $lastMonth->month);
            })
            ->select(
                DB::raw('SUM(base_amount) as totalTransaction'),
                DB::raw('SUM(charge) as totalCharge'),
                DB::raw('DATE_FORMAT(created_at,"Day %d") as date')
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->get()->map(function ($item) use ($dailyTransaction, $dailyProfit) {
                $dailyTransaction->put($item['date'], $item['totalTransaction']);
                $dailyProfit->put($item['date'], $item['totalCharge']);
            });

        return response()->json([
            "totalTransaction" => currencyPosition($dailyTransaction->sum()),
            "totalProfit" => currencyPosition($dailyProfit->sum()),
            "dailyTransaction" => $dailyTransaction,
            "dailyProfit" => $dailyProfit,
        ]);
    }
    public function transactionSearch(Request $request, $code)
    {
        $search = $request->search['value'];
        $filterTransactionId = $request->filterTransactionID;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transaction = Transaction::query()->with(['user:id,username,firstname,lastname,image,image_driver'])
            ->where('currency',$code)
            ->whereHas('user')
            ->orderBy('id', 'DESC')
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('trx_id', 'LIKE', "%$search%")
                        ->orWhere('remarks', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%{$search}%")
                                ->orWhere('username', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when(!empty($request->filterDate) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filterDate) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when(!empty($filterTransactionId), function ($query) use ($filterTransactionId) {
                return $query->where('trx_id', $filterTransactionId);
            });

        return DataTables::of($transaction)
            ->addColumn('no', function ($item) {
                static $counter = 0;
                $counter++;
                return $counter;
            })
            ->addColumn('trx', function ($item) {
                return $item->trx_id;
            })
            ->addColumn('user', function ($item) {
                $url = route("admin.user.view.profile", optional($item->user)->id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                                <div class="flex-shrink-0">
                                    ' . optional($item->user)->profilePicture() . '
                                </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->user)->fullname() . '</h5>
                                  <span class="fs-6 text-body">@' . optional($item->user)->username ?? 'Unknown' . '</span>
                                </div>
                        </a>';
            })
            ->addColumn('amount', function ($item) {
                $statusClass = $item->trx_type == '+' ? 'text-success' : 'text-danger';
                return "<h6 class='mb-0 $statusClass '>" . $item->trx_type. currencyPosition(getAmount($item->base_amount)) . "</h6>";
            })
            ->addColumn('charge', function ($item) {
                return "<span class='text-danger'>" . currencyPosition(getAmount($item->charge)) . "</span>";
            })
            ->addColumn('remarks', function ($item) {
                return $item->remarks;
            })
            ->addColumn('date-time', function ($item) {
                return dateTime($item->created_at, 'd M Y h:i A');
            })
            ->rawColumns(['user', 'amount', 'charge'])
            ->make(true);
    }


    public function dayList()
    {
        $totalDays = Carbon::now()->endOfMonth()->format('d');
        $daysByMonth = [];
        for ($i = 1; $i <= $totalDays; $i++) {
            array_push($daysByMonth, ['Day ' . sprintf("%02d", $i) => 0]);
        }
        return collect($daysByMonth)->collapse();
    }

}

