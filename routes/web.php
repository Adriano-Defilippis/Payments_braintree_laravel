<?php
// !! DA AGGIUNGERE SE NON SI CREA IL CONTROLLER
use Illuminate\Http\Request;


Route::get('/', function () {

    // Crea nuvo oggetto classe Braintree/gateway
    $gateway = new Braintree\Gateway([
          'environment' => config('services.braintree.environment'),
          'merchantId' => config('services.braintree.merchantId'),
          'publicKey' => config('services.braintree.publicKey'),
          'privateKey' => config('services.braintree.privateKey')
      ]);

      // GENERO CLIENT TOKEN DA PASSARE COME PARAMETRO NEL FRONTEND
      //TRAMITE BLADE NELLO SCRIPT

      // $token = $gateway->ClientToken()->generate();

      // PER TEST, TOKEN STATICO ACCOUNT BRAINTREE
      $token = "sandbox_7bgcfdq8_hstckbs9tty2wg8q";

      return view('welcome', [
      'token' => $token
  ]);
});

Route::post('/checkout', function(Request $request){

      $gateway = new Braintree\Gateway([
            'environment' => config('services.braintree.environment'),
            'merchantId' => config('services.braintree.merchantId'),
            'publicKey' => config('services.braintree.publicKey'),
            'privateKey' => config('services.braintree.privateKey')
        ]);

      $nonceFromTheClient = $request-> payment_method_nonce;
      $amount = $request-> amount;


      $result = $gateway->transaction()->sale([
        'amount' => $amount,
        'paymentMethodNonce' => $nonceFromTheClient,
        'customer' => [
          'firstName' => 'Tony',  //POSSONO ESSERE RIPORTATI I DATI DI CHI EFFETTUA IL PAGAMENTO
          'lastName' => 'Seppia',
          'email' => 'tony.seppia@gmail.com'
        ],
        'options' => [
          'submitForSettlement' => True
        ]
      ]);

      if ($result->success) {

        $tansaction = $result->transaction;

        // TODO: TRAMITE ARRAY RESULT posso estrapolare varie informazioni sulla transazione e salvarle nel database,

        //ad esempio posso salvare l'id della transazione e metterlo nel database per tracciabilità
        return back()
          ->with('success_message', 'Transazione avvenuta con successo. ID transazione:' . $tansaction -> id);
        // return back()->with(dd($result));
      } else {
          $errorString = "";
          foreach ($result->errors->deepAll() as $error) {
              $errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
          }

          return back()->withErrors('An error occurred with the message: ' . $result->message);
      }
  });

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
