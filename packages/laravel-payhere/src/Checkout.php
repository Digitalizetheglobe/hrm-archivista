<?php

namespace Lahirulhr\PayHere;

use Illuminate\Http\Response;

class Checkout
{
    protected array $data = [];

    protected string $successUrl = '';

    protected string $failUrl = '';

    public function data(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setOptionalData(array $data = []): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function successUrl(string $url): self
    {
        $this->successUrl = $url;

        return $this;
    }

    public function failUrl(string $url): self
    {
        $this->failUrl = $url;

        return $this;
    }

    public function renderView(): Response
    {
        $endpoint = rtrim((string) config('payhere.api_endpoint'), '/') . '/pay/checkout';

        $fields = array_merge($this->data, [
            'merchant_id' => config('payhere.merchant_id'),
            'return_url' => $this->successUrl,
            'cancel_url' => $this->failUrl,
            'notify_url' => $this->successUrl,
        ]);

        $inputs = '';
        foreach ($fields as $name => $value) {
            $inputs .= '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">' . "\n";
        }

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>PayHere</title></head><body>'
            . '<p>Redirecting to PayHere...</p>'
            . '<form id="payhere-checkout" method="post" action="' . e($endpoint) . '">'
            . $inputs
            . '</form><script>document.getElementById("payhere-checkout").submit();</script></body></html>';

        return response($html);
    }
}
