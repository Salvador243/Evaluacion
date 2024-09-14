<?php

namespace App\Http\Controllers;

use App\Models\DEP;
use App\Models\POP_WS;
use App\Models\POP_WSTURNO;
use App\Models\VEND20;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class workstationController extends Controller
{
    public function getWS(){
        $ws = POP_WS::orderBy('ID')
            ->get();
        
        return response()
            ->json([
                'success' => true,
                'data' => $ws
            ], 200);
    }

    public function getWsTurno(){
        $ws_turno = POP_WSTURNO::orderBy('WS_ID')
            ->orderBy('DIASEM')
            ->orderBy('INICIO')
            ->get();

        $ws_sem = [];
        foreach($ws_turno as $ws){
            if(!isset($ws_sem[$ws->WS_ID])){
                $ws_sem[$ws->WS_ID] = [];
            }
            if(!isset($ws_sem[$ws->WS_ID][$ws->DIASEM])){
                $ws_sem[$ws->WS_ID][$ws->DIASEM] = [];
            }
            
            if(isset($ws_sem[$ws->WS_ID][$ws->DIASEM]['horas'])){
                $horas = $ws_sem[$ws->WS_ID][$ws->DIASEM]['horas'];
            }else{
                $horas = [];
            }
                
            $inicio = new DateTime($ws->INICIO);
            $inicio = (int) $inicio->format('H');

            $fin = new DateTime($ws->FIN);
            $fin = (int) $fin->format('H');

            for($i = $inicio; $i <= $fin; $i++){
                $horas[$i] = $ws->ID;
            }

            $ws_sem[$ws->WS_ID][$ws->DIASEM]['data'][] = $ws;
            $ws_sem[$ws->WS_ID][$ws->DIASEM]['horas'] = $horas;
        }
        
        return response()
            ->json([
                'success' => true,
                'data' => $ws_sem
            ], 200);   
    }

    public function postWsTurno(Request $req){
        $id = $req->input('ID');
    
        if (!$id) {
            return response()->json(['success'=> false, 'message'=> 'ID no proporcionado'], 400);
        }

        $turno = POP_WSTURNO::where('WS_ID', $req->input('WS_ID'))
            ->where('DIASEM', $req->input('DIASEM'))
            ->where(function ($query) use ($req) {
                // Condición 1: El INICIO de algún registro está dentro del nuevo intervalo
                $query->whereTime('INICIO', '<=', $req->input('FIN') . ":00:00")
                      ->whereTime('FIN', '>=', $req->input('INICIO') . ":00:00");

                // Condición 2: El FIN de algún registro está dentro del nuevo intervalo
                $query->orWhere(function ($subquery) use ($req) {
                    $subquery->whereTime('INICIO', '<=', $req->input('FIN') . ":00:00")
                             ->whereTime('FIN', '>=', $req->input('INICIO') . ":00:00");
                });

                // Condición 3: Si el INICIO del nuevo turno es menor o igual al INICIO de algún turno existente
                $query->orWhere(function ($subquery) use ($req) {
                    $subquery->whereTime('INICIO', '>=', $req->input('INICIO') . ":00:00")
                             ->whereTime('INICIO', '<=', $req->input('FIN') . ":00:00");
                });
            })
            ->where('ID', '!=', $id)
            ->get();

        if(count($turno) > 0) {
            return response()->json(['success'=> false, 'message'=> 'Las fechas se juntan con otro horario'], 500);
        }
        
        $updated = DB::table('POP_WSTURNO')
        ->where('ID', $id)
        ->update([
            'WS_ID' => $req->input('WS_ID'),
            'DIASEM' => $req->input('DIASEM'),
            'INICIO' => str_pad($req->input('INICIO'), 2, '0', STR_PAD_LEFT) . ":00:00",
            'FIN' => str_pad($req->input('FIN'), 2, '0', STR_PAD_LEFT) . ":00:00",
            'TIPO_RESP' => $req->input('TIPO_RESP'),
            'CVE_RESP' => $req->input('CVE_RESP'),
            'CVE_DEP' => $req->input('CVE_DEP'),
        ]);

        if ($updated) {
            $pop_wsturno = DB::table('POP_WSTURNO')->where('ID', $id)->first();

            return response()->json(['success'=> true, 'data'=> $pop_wsturno], 200);
        } else {
            return response()->json(['success'=> false, 'message'=> 'No se pudo actualizar el registro'], 500);
        }
    }


    public function postWsTurnoAdd(Request $req){

        $last_id = (int) POP_WSTURNO::orderBy('ID', 'desc')->get()->first()->ID + 1;
        $turno = POP_WSTURNO::where('WS_ID', $req->input('WS_ID'))
            ->where('DIASEM', $req->input('DIASEM'))
            ->where(function ($query) use ($req) {
                // Condición 1: El INICIO de algún registro está dentro del nuevo intervalo
                $query->whereTime('INICIO', '<=', $req->input('FIN') . ":00:00")
                      ->whereTime('FIN', '>=', $req->input('INICIO') . ":00:00");

                // Condición 2: El FIN de algún registro está dentro del nuevo intervalo
                $query->orWhere(function ($subquery) use ($req) {
                    $subquery->whereTime('INICIO', '<=', $req->input('FIN') . ":00:00")
                             ->whereTime('FIN', '>=', $req->input('INICIO') . ":00:00");
                });

                // Condición 3: Si el INICIO del nuevo turno es menor o igual al INICIO de algún turno existente
                $query->orWhere(function ($subquery) use ($req) {
                    $subquery->whereTime('INICIO', '>=', $req->input('INICIO') . ":00:00")
                             ->whereTime('INICIO', '<=', $req->input('FIN') . ":00:00");
                });
            })
            ->where('ID', '!=', $req->input('ID'))
            ->get();

        if(count($turno) > 0) {
            return response()->json(['success'=> false, 'message'=> 'Las fechas se juntan con otro horario'], 500);
        }
        

        $id = DB::table('POP_WSTURNO')->insertGetId([
            'ID' => $last_id,
            'WS_ID' => $req->input('WS_ID'),
            'DIASEM' => $req->input('DIASEM'),
            'INICIO' => str_pad($req->input('INICIO'), 2, '0', STR_PAD_LEFT) . ":00:00",
            'FIN' => str_pad($req->input('FIN'), 2, '0', STR_PAD_LEFT) . ":00:00",
            'TIPO_RESP' => $req->input('TIPO_RESP'),
            'CVE_RESP' => $req->input('CVE_RESP', null),
            'CVE_DEP' => $req->input('CVE_DEP', null),
        ]);
    
        return response()->json(['success' => true, 'data' => $id], 201);
        if ($id) {    
            
        } else {
            return response()->json(['success' => false, 'message' => 'No se pudo crear el registro'], 500);
        }
    } 

    public function postWs(Request $req){
        $last_id = (int) substr(POP_WS::orderBy('ID', 'desc')->get()->first()->ID, 2) + 1;

        $new = new POP_WS();
        $zeros = str_pad($last_id, 3, '0', STR_PAD_LEFT);
        $new->ID = "WS{$zeros}";
        $new->NOMBRE = $req->input('NOMBRE');
        $new->QUICKLOG = (int)$req->input('QUICKLOG');
        $new->SUBCONTRACT = (int) $req->input('SUBCONTRACT');
        $new->CVE_VEND = $req->input('CVE_VEND');
        $new->save();
        
        if ($new) {
            return response()->json(['success'=> true, 'data'=> $new], 200);
        } else {
            return response()->json(['success'=> false, 'message'=> 'No se pudo crear el registro'], 500);
        }
    }
    public function getWsTurnoBy($id){
        $ws_turno = POP_WSTURNO::where('ID', $id)
            ->first();
    
        return response()
            ->json([
                'success' => true,
                'data' => $ws_turno
            ], 200);  
    }

    public function getVend(){
        $vend = VEND20::all();
    
        return response()
            ->json([
                'success' => true,
                'data' => $vend
            ], 200);
    }

    public function getDep(){
        $dep = DEP::all();
    
        return response()
            ->json([
                'success' => true,
                'data' => $dep
            ], 200);
    }
}
