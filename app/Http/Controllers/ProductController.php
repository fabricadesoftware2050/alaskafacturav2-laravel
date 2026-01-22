<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Municipio;
use Illuminate\Support\Facades\Cache;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        try {
            


            return response()->json(json_decode('
            [{
        "id": "1",
        "category": {
            "id": "5150",
            "name": "Ventas"
        },
        "hasNoIvaDays": false,
        "itemCategory": {
            "id": 20,
            "name": "ASEO",
            "description": "",
            "status": "active"
        },
        "name": "APROVECHAMIENTO ASEO",
        "description": "Tarifa de Aprovechamiento Aseo",
        "reference": "TAASE",
        "status": "active",
        "calculationScale": 6,
        "price": [
            {
                "idPriceList": "019be102-76b8-723e-89bc-1cc4ead0a750",
                "name": "General",
                "type": "amount",
                "price": 1500,
                "currency": {
                    "code": "COP",
                    "symbol": "$"
                },
                "main": true,
                "edited": false
            }
        ],
        "inventory": {
            "unit": "unit"
        },
        "tax": [],
        "customFields": [],
        "productKey": null,
        "type": "service",
        "itemType": null
    },
    {
        "id": "2",
        "category": {
            "id": "5150",
            "name": "Ventas"
        },
        "hasNoIvaDays": false,
        "itemCategory": {
            "id": 20,
            "name": "ASEO",
            "description": "",
            "status": "active"
        },
        "name": "BARRIDO Y LIMPIEZA ASEO",
        "description": "Tarifa de Barrido y Limpieza Aseo",
        "reference": "TBLASE",
        "status": "active",
        "calculationScale": 6,
        "price": [
            {
                "idPriceList": "019be102-76b8-723e-89bc-1cc4ead0a750",
                "name": "General",
                "type": "amount",
                "price": 800,
                "currency": {
                    "code": "COP",
                    "symbol": "$"
                },
                "main": true,
                "edited": false
            }
        ],
        "inventory": {
            "unit": "unit"
        },
        "tax": [],
        "customFields": [],
        "productKey": null,
        "type": "service",
        "itemType": null
    }]'));

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al listar municipios ' . $e->getMessage()], 500);
        }

  

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
