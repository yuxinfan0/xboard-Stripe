<?php

namespace Plugin\Stripe;

use App\Services\Plugin\AbstractPlugin;
use App\Contracts\PaymentInterface;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class Plugin extends AbstractPlugin implements PaymentInterface
{
    public function boot(): void
    {
        $this->filter('available_payment_methods', function ($methods) {
            if ($this->getConfig('enabled', true)) {
                $methods['Stripe'] = [
                    'name' => $this->getConfig('display_name', 'Stripe'),
                    'icon' => $this->getConfig('icon', '💳'),
                    'plugin_code' => $this->getPluginCode(),
                    'type' => 'plugin'
                ];
            }
            return $methods;
        });
    }

    public function form(): array
    {
        return [
            'currency' => [
                'label' => '货币单位',
                'type' => 'string',
                'description' => '如: cny, usd, hkd',
                'default' => 'cny',
                'required' => true
            ],
            'stripe_sk_live' => [
                'label' => 'Stripe Secret Key',
                'type' => 'text',
                'description' => 'sk_live_...',
                'required' => true
            ],
            'stripe_webhook_key' => [
                'label' => 'WebHook 密钥',
                'type' => 'text',
                'description' => 'whsec_...',
                'required' => true
            ],
            'payment_method_types' => [
                'label' => '支付方式',
                'type' => 'string',
                'description' => '英文逗号分割，如: card,alipay refer: https://docs.stripe.com/api/payment_methods/object#payment_method_object-type',
                'default' => 'card',
                'required' => true
            ]
        ];
    }

    public function pay($order): array
    {
        Stripe::setApiKey($this->getConfig('stripe_sk_live'));
        $currency = $this->getConfig('currency', 'cny');
        $exchange = $this->config['exchange'] ?? 1;

        $params = [
            'payment_method_types' => explode(',', $this->getConfig('payment_method_types', 'card')),
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => admin_setting('app_name', 'XBoard') . ' - 订阅',
                    ],
                    'unit_amount' => floor($order['total_amount']),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $order['return_url'],
            'cancel_url' => $order['return_url'],
            'client_reference_id' => $order['trade_no'],
        ];

        try {
            $session = Session::create($params);
            return [
                'type' => 1,
                'data' => $session->url
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function notify($params): array|bool
    {
        Stripe::setApiKey($this->getConfig('stripe_sk_live'));
        $payload = request()->getContent();
        $sig_header = request()->header('Stripe-Signature');
        $endpoint_secret = $this->getConfig('stripe_webhook_key');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            abort(400);
        } catch (SignatureVerificationException $e) {
            abort(400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            return [
                'trade_no' => $session->client_reference_id,
                'callback_no' => $session->payment_intent
            ];
        }
        
        return true;
    }
}