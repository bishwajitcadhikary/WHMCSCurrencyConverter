<?php

namespace WHMCS\Module\Addon\WovoCurrencyConverter\Admin;

class Controller
{

    public function index($vars)
    {
        $apiKey = $vars['apiKey'];
        $currencies = $vars['currencies'];
        $baseCurrency = $vars['baseCurrency'];

        $setCurrencies = "";
        $date = \Carbon\Carbon::now()->toDayDateTimeString();
        $html = "";

        try {
            $configuration = \WHMCS\Database\Capsule::table("tbladdonmodules")->where("module", "=", "wovocurrencyconverter")->get();
            if ($configuration) {
                foreach ($configuration as $obj) {
                    if ($obj->setting == "currencies") {
                        $setCurrencies = explode(",", $obj->value);
                    }
                    if ($obj->setting == "baseCurrency") {
                        $baseCurrency = $obj->value;
                    }
                    if ($obj->setting == "apiKey") {
                        $apiKey = $obj->value;
                    }
                }
                if (empty($apiKey)) {
                    return '<div class="alert alert-danger">Currency Converter API Key Not Defined !</div>';

                }
                $curl = curl_init();
                curl_setopt_array($curl, [CURLOPT_URL => "http://data.fixer.io/api/latest?access_key=" . $apiKey, CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "GET", CURLOPT_HTTPHEADER => ["cache-control: no-cache"]]);
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                $allCurrencies = [];
                if ($err) {
                    return '<div class="alert alert-danger">cURL Error #:' . $err . '</div>';
                }
                file_put_contents(__DIR__ . "/../../" . "rates.php", "<?php header(\"Access-Control-Allow-Origin:*\");?>" . $response);
                $response = json_decode($response);
                if (!$response->success) {
                    $error = $response->error;
                    if ($error->type) {
                        echo '<div class="alert alert-danger">' . $error->type . '</div>';
                        if ($error->info) {
                            echo '<div class="alert alert-danger">' . $error->info . '</div>';
                        }
                    } else {
                        return "Currency API Response Failed !";
                    }
                }
                $rates = json_decode(json_encode($response->rates), true);
                foreach ($rates as $key => $value) {
                    if (in_array($key, $setCurrencies)) {
                        $newValue = $rates['EUR'] / $rates[$baseCurrency] * $value;
                        \WHMCS\Database\Capsule::table("tblcurrencies")->where("code", "=", $key)->update(["rate" => $newValue]);
                        $allCurrencies[$key] = $newValue;
                    }
                }

                $html .= <<<EOF
                <div class="text-center">
                     <h1>Currency Converter Rates</h1>
                     <strong>API Key:</strong> $apiKey<br>
                     <strong>Currencies:</strong> $currencies<br>
                     <strong>Base Currency:</strong> $baseCurrency<br>
                    <strong>Updated at : $date </strong>            
                </div>    
                EOF;


                $html .= <<<EOF
                <div class="table-responsive">
                  <table class="table table-striped table-bordered table-hover" id="wovocurrencyconverter_table">
                    <thead>
                      <tr>
                        <th>Currency Code</th>
                        <th>Rate</th>
                      </tr>
                    </thead>
                    <tbody>
                EOF;

                foreach ($allCurrencies as $key => $val) {
                    $html .= <<<EOF
                      <tr>
                        <td> $key </td>
                        <td> $val </td>
                      </tr>
                EOF;
                }
                $html .= <<<EOF
                    </tbody>
                  </table>
                </div>
                <script >
                    jQuery(document).ready(function() {
                       var table = jQuery("#wovocurrencyconverter_table").DataTable({
                            "responsive": true,
                            "processing": true
                        });
                    })                
                </script>
                EOF;

                return $html;
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            echo $ex->getMessage();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}