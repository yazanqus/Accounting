<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\Invoice;
use App\InvoicePayment;
use App\Order;
use App\Plan;
use App\UserCoupon;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MolliePaymentController extends Controller
{

    public $api_key;
    public $profile_id;
    public $partner_id;
    public $is_enabled;

    public function paymentConfig()
    {
        if(\Auth::user()->type == 'company')
        {
            $payment_setting = Utility::getAdminPaymentSetting();
        }
        else
        {
            $payment_setting = Utility::getCompanyPaymentSetting();
        }

        $this->api_key    = isset($payment_setting['mollie_api_key']) ? $payment_setting['mollie_api_key'] : '';
        $this->profile_id = isset($payment_setting['mollie_profile_id']) ? $payment_setting['mollie_profile_id'] : '';
        $this->partner_id = isset($payment_setting['mollie_partner_id']) ? $payment_setting['mollie_partner_id'] : '';
        $this->is_enabled = isset($payment_setting['is_mollie_enabled']) ? $payment_setting['is_mollie_enabled'] : 'off';

        return $this;
    }


    public function planPayWithMollie(Request $request)
    {
        $payment    = $this->paymentConfig();
        $planID     = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan       = Plan::find($planID);
        $authuser   = Auth::user();
        $coupons_id = '';
        if($plan)
        {
            $price = $plan->price;
            if(isset($request->coupon) && !empty($request->coupon))
            {
                $request->coupon = trim($request->coupon);
                $coupons         = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if(!empty($coupons))
                {
                    $usedCoupun             = $coupons->used_coupon();
                    $discount_value         = ($price / 100) * $coupons->discount;
                    $plan->discounted_price = $price - $discount_value;
                    $coupons_id             = $coupons->id;
                    if($usedCoupun >= $coupons->limit)
                    {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    $price = $price - $discount_value;
                }
                else
                {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }
            }

            if($price <= 0)
            {
                $authuser->plan = $plan->id;
                $authuser->save();

                $assignPlan = $authuser->assignPlan($plan->id);

                if($assignPlan['is_success'] == true && !empty($plan))
                {

                    $orderID = time();
                    Order::create(
                        [
                            'order_id' => $orderID,
                            'name' => null,
                            'email' => null,
                            'card_number' => null,
                            'card_exp_month' => null,
                            'card_exp_year' => null,
                            'plan_name' => $plan->name,
                            'plan_id' => $plan->id,
                            'price' => $price == null ? 0 : $price,
                            'price_currency' => !empty(env('CURRENCY')) ? env('CURRENCY') : 'USD',
                            'txn_id' => '',
                            'payment_type' => __('Mollie'),
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $authuser->id,
                        ]
                    );
                    $assignPlan = $authuser->assignPlan($plan->id);

                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Plan fail to upgrade.'));
                }
            }

            $mollie = new \Mollie\Api\MollieApiClient();
            $mollie->setApiKey($this->api_key);

            $payment = $mollie->payments->create(
                [
                    "amount" => [
                        "currency" => env('CURRENCY'),
                        "value" => number_format($price, 2),
                    ],
                    "description" => "payment for product",
                    "redirectUrl" => route(
                        'plan.mollie', [
                                         $request->plan_id,
                                         'coupon_id=' . $coupons_id,
                                     ]
                    ),
                ]
            );

            session()->put('mollie_payment_id', $payment->id);

            return redirect($payment->getCheckoutUrl())->with('payment_id', $payment->id);
        }
        else
        {
            return redirect()->back()->with('error', 'Plan is deleted.');
        }

    }

    public function getPaymentStatus(Request $request, $plan)
    {
        $payment = $this->paymentConfig();

        $planID  = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan    = Plan::find($planID);
        $user    = Auth::user();
        $orderID = time();
        if($plan)
        {
            try
            {
                $mollie = new \Mollie\Api\MollieApiClient();
                $mollie->setApiKey($this->api_key);

                if(session()->has('mollie_payment_id'))
                {
                    $payment = $mollie->payments->get(session()->get('mollie_payment_id'));

                    if($payment->isPaid())
                    {

                        if($request->has('coupon_id') && $request->coupon_id != '')
                        {
                            $coupons = Coupon::find($request->coupon_id);

                            if(!empty($coupons))
                            {
                                $userCoupon         = new UserCoupon();
                                $userCoupon->user   = $user->id;
                                $userCoupon->coupon = $coupons->id;
                                $userCoupon->order  = $orderID;
                                $userCoupon->save();

                                $usedCoupun = $coupons->used_coupon();
                                if($coupons->limit <= $usedCoupun)
                                {
                                    $coupons->is_active = 0;
                                    $coupons->save();
                                }
                            }
                        }

                        $order                 = new Order();
                        $order->order_id       = $orderID;
                        $order->name           = $user->name;
                        $order->card_number    = '';
                        $order->card_exp_month = '';
                        $order->card_exp_year  = '';
                        $order->plan_name      = $plan->name;
                        $order->plan_id        = $plan->id;
                        $order->price          = isset($request->TXNAMOUNT) ? $request->TXNAMOUNT : 0;
                        $order->price_currency = env('CURRENCY');
                        $order->txn_id         = isset($request->TXNID) ? $request->TXNID : '';
                        $order->payment_type   = __('Mollie');
                        $order->payment_status = 'success';
                        $order->receipt        = '';
                        $order->user_id        = $user->id;
                        $order->save();

                        $assignPlan = $user->assignPlan($plan->id, $request->payment_frequency);

                        if($assignPlan['is_success'])
                        {
                            return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                        }
                        else
                        {
                            return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                        }
                    }
                    else
                    {
                        return redirect()->route('plan.index')->with('error', __('Transaction has been failed! '));
                    }
                }
                else
                {
                    return redirect()->route('plan.index')->with('error', __('Transaction has been failed! '));
                }
            }
            catch(\Exception $e)
            {
                return redirect()->route('plan.index')->with('error', __('Plan not found!'));
            }
        }
    }

    public function invoicePayWithMollie(Request $request)
    {
        $payment   = $this->paymentConfig();
        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($request->invoice_id);
        $invoice   = Invoice::find($invoiceID);

        if($invoice)
        {
            $price = $request->amount;
            if($price > 0)
            {
                $mollie = new \Mollie\Api\MollieApiClient();
                $mollie->setApiKey($this->api_key);

                $payment = $mollie->payments->create(
                    [
                        "amount" => [
                            "currency" => Utility::getValByName('site_currency'),
                            "value" => number_format($price, 2),
                        ],
                        "description" => "payment for product",
                        "redirectUrl" => route(
                            'customer.invoice.mollie', [
                                                $request->invoice_id,
                                                $price,
                                            ]
                        ),
                    ]
                );

                session()->put('mollie_payment_id', $payment->id);

                return redirect($payment->getCheckoutUrl())->with('payment_id', $payment->id);

            }
            else
            {
                $res['msg']  = __("Enter valid amount.");
                $res['flag'] = 2;

                return $res;
            }


        }
        else
        {
            return redirect()->back()->with('error', 'Invoice is deleted.');
        }

    }

    public function getInvoicePaymentStatus(Request $request, $invoice_id, $amount)
    {

        $payment = $this->paymentConfig();

        $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));
        $settings = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');

        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
        $invoice   = Invoice::find($invoiceID);
        $result    = array();

        if($invoice)
        {
            try
            {
                $mollie = new \Mollie\Api\MollieApiClient();
                $mollie->setApiKey($this->api_key);

                if(session()->has('mollie_payment_id'))
                {
                    $payment = $mollie->payments->get(session()->get('mollie_payment_id'));

                    if($payment->isPaid())
                    {


                        $payments = InvoicePayment::create(
                            [
                                'invoice' => $invoice->id,
                                'date' => date('Y-m-d'),
                                'amount' => $amount,
                                'payment_method' => 1,
                                'transaction' => $orderID,
                                'payment_type' => __('Mollie'),
                                'receipt' => '',
                                'notes' => __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                            ]
                        );

                        $invoice = Invoice::find($invoice->id);

                        if($invoice->getDue() <= 0.0)
                        {
                            Invoice::change_status($invoice->id, 5);
                        }
                        elseif($invoice->getDue() > 0)
                        {
                            Invoice::change_status($invoice->id, 4);
                        }
                        else
                        {
                            Invoice::change_status($invoice->id, 3);
                        }

                        return redirect()->back()->with('success', __(' Payment successfully added.'));

                    }
                    else
                    {
                        return redirect()->back()->with('error', __('Transaction has been failed! '));
                    }
                }
                else
                {
                    return redirect()->back()->with('error', __('Transaction has been failed! '));
                }
            }
            catch(\Exception $e)
            {
                return redirect()->back()->with('error', __('Invoice not found!'));
            }
        }
    }
}
