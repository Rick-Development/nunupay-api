<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\MoneyTransfer;
use App\Models\Transaction;
use App\Models\UserWallet;
use App\Traits\ManageWallet;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MoneyTransferController extends Controller
{
    use Upload, Notify, ManageWallet;

    public function transferList()
    {
        $transferRecord = \Cache::get('transferRecord');
        if (!$transferRecord) {
            $transferRecord = MoneyTransfer::selectRaw('COUNT(id) AS totalTransfer')
                ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pending')
                ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS pendingPercentage')
                ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS completed')
                ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS completedPercentage')
                ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS underReview')
                ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(id)) * 100 AS underReviewPercentage')
                ->selectRaw('COUNT(CASE WHEN status = 3 THEN id END) AS canceled')
                ->selectRaw('(COUNT(CASE WHEN status = 3 THEN id END) / COUNT(id)) * 100 AS canceledPercentage')
                ->get()
                ->toArray();
            \Cache::put('transferRecord', $transferRecord);
        }
        return view('admin.money_transfer.list', compact('transferRecord'));
    }


    public function transferSearch(Request $request)
    {
        $search = $request->search['value'];
        $filterService = $request->subject;
        $filterStatus = $request->filterStatus;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transferHistory = MoneyTransfer::query()
            ->with('user:id,username,firstname,lastname,image,image_driver')
            ->whereHas('user')
            ->whereIn('status', [1, 2, 3])
            ->orderBy('id', 'DESC')
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('trx_id', 'LIKE', "%$search%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%{$search}%")
                                ->orWhere('username', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when(!empty($filterService), function ($query) use ($filterService) {
                return $query->where(function ($subquery) use ($filterService) {
                    $subquery->whereHas('service', function ($q) use ($filterService) {
                        $q->where('name', 'LIKE', "%$filterService%");
                    });
                });
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus == "all") {
                    return $query->where('status', '!=', null);
                }
                return $query->where('status', $filterStatus);
            })
            ->when(!empty($request->filterDate) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filterDate) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });

        return DataTables::of($transferHistory)
            ->addColumn('no', function () {
                static $counter = 0;
                $counter++;
                return $counter;
            })
            ->addColumn('trx_id', function ($item) {
                return '#' . $item->trx_id;
            })
            ->addColumn('sender', function ($item) {
                $url = route("admin.user.edit", optional($item->user)->id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0"> ' . optional($item->user)->profilePicture() . ' </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional($item->user)->fullname() . '</h5>
                              <span class="fs-6 text-body">@' . optional($item->user)->username . '</span>
                            </div>
                        </a>';
            })
            ->addColumn('receiver', function ($item) {
                return ' <div class="flex-grow-1 ms-3">
                            <h5 class="text-hover-primary mb-0">' . optional($item->recipient)->name . '</h5>
                            <span class="fs-6 text-body">' . optional($item->recipient)->email . '</span>
                        </div>';

            })
            ->addColumn('status', function ($item) {
                if ($item->status == 0) {
                    return ' <span class="badge bg-soft-warning text-warning legend-indicator">
                                    <span class="legend-indicator bg-warning"></span> ' . trans('Draft') . '
                                </span>';
                } else if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">
                                <span class="legend-indicator bg-success"></span> ' . trans('Completed') . '
                            </span>';

                } else if ($item->status == 2) {
                    return '<span class="badge bg-soft-info text-info">
                                    <span class="legend-indicator bg-info"></span> ' . trans('Under Review') . '
                                </span>';

                } else if ($item->status == 3) {
                    return '<span class="badge bg-soft-danger text-danger">
                                    <span class="legend-indicator bg-danger"></span> ' . trans('Rejected') . '
                                </span>';
                }
            })
            ->addColumn('paid_at', function ($item) {
                return dateTime($item->created_at);
            })
            ->addColumn('action', function ($item) {
                $url = route('admin.transferView', $item->uuid);
                return '<a class="btn btn-white btn-sm" href="' . $url . '">
                      <i class="bi-eye"></i> ' . trans("View") . '
                    </a>';
            })
            ->rawColumns(['trx_id', 'sender', 'receiver', 'status', 'paid_at', 'action'])
            ->make(true);
    }


    public function transferView($uuid)
    {
        $transferDetails = MoneyTransfer::where('uuid', $uuid)
            ->where('status', '!=', 0)
            ->with('user:id,username,firstname,lastname,email,image,image_driver')->firstOrFail();
        $data['status'] = $transferDetails->status;
        $data['transfer'] = $transferDetails;
        return view('admin.money_transfer.view', $data);
    }

    private function createTransaction($wallet, $amount, $type, $remarks): Transaction
    {
        $currency = $wallet->currency_code;
        $rate = $this->getCurrencyRate(basicControl()->base_currency, $currency);
        $baseAmount = $amount * $rate;

        $transaction = new Transaction();
        $transaction->user_id = $wallet->user_id;
        $transaction->wallet_id = $wallet->id ?? null;
        $transaction->amount = $amount;
        $transaction->base_amount = $baseAmount;
        $transaction->currency = $currency;
        $transaction->charge = 0;
        $transaction->balance = getAmount($wallet->balance ?? $transaction->balance ?? 0);
        $transaction->trx_type = $type;
        $transaction->trx_id = strRandom();
        $transaction->remarks = $remarks;
        return $transaction;
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $transfer = MoneyTransfer::findOrFail($id);

            $rcpUserId = $transfer->r_user_id ?? null;
            if ($rcpUserId) {
                $rcpWallet = UserWallet::where('user_id', $rcpUserId)
                    ->where('currency_code', $transfer->receiver_currency)
                    ->first();
                if (!$rcpWallet) {
                    $rcpWallet = UserWallet::where('user_id', $rcpUserId)
                        ->where('default', 1)
                        ->firstOr(function () use ($transfer, $rcpUserId, &$response) {
                            return UserWallet::where('user_id', $rcpUserId)
                                ->firstOr(function () {
                                    return back()->with('error', 'Invalid recipient wallet');
                                });
                        });
                }
                $rcpCurrRate = $this->getCurrencyRate($rcpWallet->currency_code, $transfer->receiver_currency);
                $rcpAmount = $transfer->recipient_get_amount * $rcpCurrRate;

                $rcpWallet->increment('balance', $rcpAmount);
                $remarks = "Money received from {$transfer->user?->fullname()}";
                $rcpTransaction = $this->createTransaction($rcpWallet, $rcpAmount, '+', $remarks);
                $rcpWallet->transactional()->save($rcpTransaction);
            }

            $transfer->status = 1;
            $transfer->payment_status = 1;
            $transfer->save();

            $user = $transfer->user;
            $params = [
                'user' => $user->fullname(),
                'transaction' => $transfer->trx_id,
            ];
            $action = [
                "link" => route('user.transferDetails', $transfer->uuid),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = "#";
            $this->sendMailSms($user, 'TRANSFER_APPROVED', $params);
            $this->userPushNotification($user, 'TRANSFER_APPROVED', $params, $action);
            $this->userFirebasePushNotification($user, 'TRANSFER_APPROVED', $params, $firebaseAction);

            DB::commit();
            return back()->with('success', 'Transfer Request has been Approved');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required',
            'resubmitted' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $transfer = MoneyTransfer::findOrFail($id);
            $userId = $transfer->sender_id;

            $deposit = $transfer->deposit;
            if (!$deposit) {
                return back()->with('error', 'Deposit History Not Found');
            }

            $wallet = $deposit->wallet_id ? UserWallet::find($deposit->wallet_id) : null;
            if (!$wallet) {
                $wallet = UserWallet::where('user_id', $userId)->where('default', 1)->firstOrFail();
            }

            /* Add money to Sender Wallet */
            $gatewayCurrency = $deposit->payment_method_currency;
            $walletCurrency = $wallet->currency_code;
            $rate = $this->getCurrencyRate($walletCurrency, $gatewayCurrency);
            $amount = $deposit->amount * $rate;
            $addBalance = $this->updateWallet($userId, $wallet->id, $amount, 1);

            $transfer->status = 3;
            $transfer->payment_status = 3;
            $transfer->resubmitted = $request->resubmitted;
            $transfer->reason = $request->reason;
            $transfer->save();
            //$deposit->delete();

            $user = $transfer->user;
            $params = [
                'user' => $user->fullname(),
                'amount' => getAmount($amount),
                'currency' => $gatewayCurrency,
                'transaction' => $transfer->trx_id,
            ];
            $action = [
                "link" => route('user.transferDetails', $transfer->uuid),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = "#";
            $this->sendMailSms($user, 'TRANSFER_CANCEL', $params);
            $this->userPushNotification($user, 'TRANSFER_CANCEL', $params, $action);
            $this->userFirebasePushNotification($user, 'TRANSFER_CANCEL', $params, $firebaseAction);

            DB::commit();

            return back()->with('success', 'Transfer Request Rejected');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


}
