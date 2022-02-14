<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \GuzzleHttp\Client;

class HealthUnit extends Model
{
    use HasFactory;


    protected $fillable = [
        'unit_code',
        'cnes_code',
        'cnpj_maintainer_code',
        'alias_company_name',
        'company_type',
        'ibge_state_id',
        'ibge_city_id',
        'address',
        'address_number',
        'address_complement',
        'neighborhood',
        'cep_code',
        'latitude',
        'longitude',
        'stock_form_death',
        'stock_form_alive'
    ];

    public function geocodeAddressFull()
    {
        $city = 'Teresina';
        $state = 'Piauí';

        $client = new Client();

        $coordinates['lat'] = null;
        $coordinates['lng'] = null;

        $url[0] = 'https://maps.googleapis.com/maps/api/geocode/json?address=' .
        urlencode(
            $this->address_number . ', ' . $this->address . ', ' . $this->neighborhood . ', ' . $city . ', ' . $state
        )
        . '&key=' . env('GOOGLE_MAPS_KEY');

        $url[1] = 'https://maps.googleapis.com/maps/api/geocode/json?address=' .
        urlencode($this->address . ', ' . $this->neighborhood . ', ' . $city . ', ' . $state)
        . '&key=' . env('GOOGLE_MAPS_KEY');

        $url[2] = 'https://maps.googleapis.com/maps/api/geocode/json?address=' .
        urlencode($this->neighborhood . ', ' . $city . ', ' . $state)
        . '&key=' . env('GOOGLE_MAPS_KEY');

        $request = $client->get($url[0]);

        $geocodeData = json_decode($request->getBody());
        if ($geocodeData->status == 'ZERO_RESULTS') {
            $request = $client->get($url[1]);
            $geocodeData = json_decode($request->getBody());

            if ($geocodeData->status == 'ZERO_RESULTS') {
                $request = $client->get($url[2]);
                $geocodeData = json_decode($request->getBody());
                echo $url[2];
            }
        }


        if (!empty($geocodeData)
            && $geocodeData->status != 'ZERO_RESULTS'
            && isset($geocodeData->results)
            && isset($geocodeData->results[0])
        ) {
            $this->latitude = $geocodeData->results[0]->geometry->location->lat;
            $this->longitude = $geocodeData->results[0]->geometry->location->lng;
        }

        $this->save();
        return $this;
    }

    public function bornAliveForms()
    {
        return $this->hasMany(BornAliveForm::class, 'cnes_code', 'cnes_code');
    }

    public function seathCertificateForms()
    {
        return $this->hasMany(DeathCertificateForm::class, 'cnes_code', 'cnes_code');
    }
}
