<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\StripeClient;
use Stripe\Webhook;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
/**
 * =========================================
 *  Stripe サブスク　コントローラー
 * =========================================
 */
class StripSubscriptionController extends Controller
{
    /**
     * サブスク申請ページ(チェックアウトに進む前のページ)
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view("stripe.subscription.index");
    }

    /**
     * 支払い完了
     *
     * @return \Illuminate\View\View
     */
    public function comp()
    {
        return view("stripe.subscription.comp");
    }

    /* ~ */
    /**
     * checkout
     *
     * @return \Illuminate\Http\Response
     */
    public function checkout()
    {
        # シークレットキーの読み込み
        Stripe::setApiKey(config("stripe.secret_key"));

        # 顧客情報
        $user = Auth::user();
        $customer = $user->createOrGetStripeCustomer();

        # 商品情報
        $price_id = config("stripe.price_id");

        $checkout_session = Session::create([
            "customer" => $customer->id, //顧客ID
            "customer_update" => ["address" => "auto"],
            "payment_method_types" => ["card"], //決済方法

            "line_items" => [
                [
                    "price" => $price_id, //商品情報
                    "quantity" => 1,
                ],
            ],

            "payment_method_options" => [
                "card" => [
                    //3Dセキュア
                    "request_three_d_secure" => "any",
                ],
            ],

            "mode" => "subscription",
            "success_url" => route("stripe.subscription.comp"), //成功リダイレクトパス
            "cancel_url" => route("stripe.subscription"), //失敗リダイレクトパス
        ]);

        return redirect()->to($checkout_session->url);
    }

    /**
     * カスタマーポータル
     *
     * @return \Illuminate\Http\Response
     */
    public function customer_portal()
    {
        Stripe::setApiKey(config("stripe.secret_key"));

        # 顧客情報
        $user = Auth::user();
        $customer = $user->createOrGetStripeCustomer();

        # Stripeクライアントを初期化
        $stripe = new StripeClient(config("stripe.secret_key"));

        # カスタマーポータルセッションを作成
        $session = $stripe->billingPortal->sessions->create([
            "customer" => $customer->id,
            "return_url" => route("home"), // ポータルを終了した後にリダイレクト
        ]);

        # カスタマーポータルへのリダイレクト
        return redirect($session->url);
    }

    /**
     * サブスク決済完了ウェブホック
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header("Stripe-Signature");
        $endpointSecret = config("stripe.subscription_endpoint_secret");

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(["error" => "Invalid payload"], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(["error" => "Invalid signature"], 400);
        }

        $session = $event->data->object;

        switch ($event->type) {
            case "customer.subscription.created":
            case "customer.subscription.updated":
                return $this->handleCustomerSubscriptionUpdate(
                    $request,
                    $session
                );
            case "customer.subscription.deleted":
                return $this->handleCustomerSubscriptionDeleted(
                    $request,
                    $session
                );
            default:
                return response()->json([], 200); // Unknown event
        }
    }

    private function handleCustomerSubscriptionUpdate(
        Request $request,
        $session
    ) {
        $user = User::where("stripe_id", $session->customer)->first();
        if (!$user) {
            return response()->json(["message" => "User not found"], 403);
        }

        $subscriptionId = $session->items->data[0]->plan->id;
        if (
            !$user->subscription_id ||
            $user->subscription_id !== $subscriptionId
        ) {
            $user->update(["subscription_id" => $subscriptionId]);
        }

        return response()->json(["user" => $user], 200);
    }

    private function handleCustomerSubscriptionDeleted(
        Request $request,
        $session
    ) {
        $user = User::where("stripe_id", $session->customer)->first();
        if (!$user) {
            return response()->json(["message" => "User not found"], 403);
        }

        if ($user->subscription_id) {
            $user->update(["subscription_id" => null]);
        }

        return response()->json(["user" => $user], 200);
    }
}
