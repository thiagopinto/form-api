<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\models\BornAliveForm;
use DateTime;

class ReceiptDateBornAliveForms extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $forms = BornAliveForm::where(function($query){
            return $query->orWhere('status', 3)->orWhere('status', 4);
        })->get();

        foreach ($forms as $form) {
            $date = new DateTime($form->updated_at);
            $form->receipt_date = $date->format('Y-m-d');
            $form->save();
        }
    }
}
