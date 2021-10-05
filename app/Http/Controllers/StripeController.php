<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Stripe;
use Flash;

class StripeController extends Controller
{
    public function stripe(Request $request)
    {
        $validateData = Validator::make($request->all(),[
            'card_no' => 'required',
            'exp_month' => 'required',
            'exp_year' => 'required',
            'cvv_no' => 'required',
            'amount' => 'required'
        ]);
     

        if ($validateData->fails()) {      

            Flash::error('ERROR!','All fields are required!');
            return back();
 
        } else {
            $stripe = Stripe::make('sk_test_51JgvNAEAUg2DA0iLRm6OY9OkrYKQtFDoyeBckIukcj27bXN1arPbOQteLIqSHLuA4D0vwSIc58vkgorqsYTZodjG00Z2xQWIMJ');
            try {
                $token = $stripe->tokens()->create([
                    'card' => [
                        'number'    => $request->card_no,
                        'exp_month' => $request->exp_month,
                        'exp_year'  => $request->exp_year,
                        'cvc'       => $request->cvv_no,
                    ],
                ]);

                if (!isset($token['id'])) {
                    Flash::error('ERROR!','The Stripe Token is not exist!');
                }

                $charge = $stripe->charges()->create([
                    'card' => $token['id'],
                    'currency' => 'USD',
                    'amount'   => $request->amount,
                    'description' => 'Added in wallet',
                ]);

                if($charge['status'] == 'succeeded') {
                    /**
                    * Write Here Your Database insert logic.
                    */
                    Flash::success('Success!','Money added successfully in wallet!');
                    return back();
                } else {
                    Flash::error('ERROR!','Money did not add in wallet!');
                    return back();
                }
                
            } catch (Exception $e) {
                Flash::error('error',$e->getMessage());
                return back();
            } catch(\Cartalyst\Stripe\Exception\CardErrorException $e) {
                Flash::error('error',$e->getMessage());
                return back();
            } catch(\Cartalyst\Stripe\Exception\MissingParameterException $e) {
                Flash::error('error',$e->getMessage());
                return back();
            }


        }
       
    }
    
}
