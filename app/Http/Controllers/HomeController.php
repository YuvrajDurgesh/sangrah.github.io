<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    //

    public function getOrderId(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://test.instamojo.com/oauth2/token/');     
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $payload = Array(
            'grant_type' => 'client_credentials',
            'client_id' => 'test_pcg4Pxrae2U1A9zvHt0NmsQVG73jbalPOlL',
            'client_secret' => 'test_U1QKHYkhf28k4UewXgTE5vu8Uj6kYJPAlO7OSMFXDPuoctnpJpptGVJOberrdBGgZyyhCSq5WfYvTz7ONIm9xPk7xZ5v3LB2bF7lZU42TVT2jJBzckBHwQGHyY1'
        );

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); 

        if ($http_status == 200 || $http_status == 201) {

            // echo $response;
            $data = json_decode($response);
            $access_token = $data->access_token;

            $requestId = $this->getPaymentRequest($access_token, $name, $email, $phone);

            // return response()->json(['message' => 'success', 'resopnse id' => $requestId]);

            if($requestId == "")
            {
                return response()->json(['message' => 'Unauthorized payement request'], 401); 
            }else{
                $orderId = $this->getOrderIdInstamojo($access_token, $requestId);

                if($orderId == "")
                {
                    return response()->json(['message' => 'Unauthorized order id'], 401); 
                }else{
                    return response()->json(['message' => 'success', 'order_id' => $orderId, 'request_id' => $requestId]);
                }
            }

        }else{
            // Return a response to the user
            return response()->json(['message' => 'Unauthorized token'], 401);
        }

        



        // Return a response to the user
        // return response()->json(['message' => 'Form submitted successfully']);
    }

    private function getPaymentRequest($access_token, $name, $email, $phone)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://test.instamojo.com/v2/payment_requests/');
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: Bearer '.$access_token));

        $payload = Array(
        'purpose' => 'Sangrah app rashifal',
        'amount' => '99',
        'buyer_name' => $name,
        'email' => $email,
        'phone' => $phone,
        'send_email' => 'False',
        'allow_repeated_payments' => 'False',
        );

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); 

        // return $http_status;

        if ($http_status == 200 || $http_status == 201) {

            // echo $response;
            $data = json_decode($response);
            $id = $data->id;

            return $id;

        }else{
            // Return a response to the user
            return "";
        }

    }


    private function getOrderIdInstamojo($access_token, $requestId)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://test.instamojo.com/v2/gateway/orders/payment-request/');
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: Bearer '.$access_token));

        $payload = Array(
            'id' => $requestId
        );

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); 

        // return $response;


        if ($http_status == 200 || $http_status == 201) {

            // echo $response;
            $data = json_decode($response);
            return $data->order_id;

        }else{
            // Return a response to the user
            return "";
        }
    }
}
