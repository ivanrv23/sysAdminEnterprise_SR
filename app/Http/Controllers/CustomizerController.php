<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomizerRequest;
use App\Http\Requests\UpdateCustomizerRequest;

use App\Models\Customizer;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CustomizerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Auth::user()->companies_id;
        return Inertia::render('Customizers/Index', [
            'customizers' => Customizer::select('customizers.*', 'companies.name')->join('companies', 'customizers.companies_id', '=', 'companies.id')->get(),
            'colors' => Customizer::where('companies_id', $company)->get(),
            'companies' => Company::all(),
            'company' => Company::find($company),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCustomizerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomizerRequest $request)
    {
        if ($request->hasFile("logo")) {

            $cod = $request->companies_id;
            $nombre = 'logo_empresa' . $cod . '.' . $request->logo->getClientOriginalExtension();
            $destino = 'img/empresa' . $cod . '/';
            $directorio = $destino . $nombre;
            $uploadSuccess = $request->logo->move($destino, $nombre);
            Customizer::create([
                'companies_id' => $request->companies_id,
                'color_menu' => $request->color_menu,
                'color_sub_menu' => $request->color_sub_menu,
                'color_header' => $request->color_header,
                'color_footer' => $request->color_footer,
                'color_text' => $request->color_text,
                'logo' => $directorio,
            ]);
        } else {
            Customizer::create([
                'companies_id' => $request->companies_id,
                'color_menu' => $request->color_menu,
                'color_sub_menu' => $request->color_sub_menu,
                'color_header' => $request->color_header,
                'color_footer' => $request->color_footer,
                'color  _text' => $request->color_text,
                'logo' => 'img/empresa/logo_empresa.png',
            ]);
        }


        return Redirect::route('customizers.index')->with('message', 'Agregado');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCustomizerRequest  $request
     * @param  \App\Models\Customizer  $customizer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCustomizerRequest $request, $id)
    {
        // return $request;
        $customizer = Customizer::find($id);
        if ($request->hasFile("logo")) {
            $cod = $request->companies_id;
            $nombre = 'logo_empresa' . $cod . '.' . $request->logo->getClientOriginalExtension();
            $destino = 'img/empresa' . $cod . '/';
            $directorio = $destino . $nombre;
            if (file_exists($destino)) {
                $files = glob($destino . '/*'); //obtenemos todos los nombres de los ficheros
                foreach ($files as $file) {
                    if (is_file($file))
                        unlink($file); //elimino el fichero
                }
            }
            $uploadSuccess = $request->logo->move($destino, $nombre);
            $customizer->update([
                'companies_id' => $request->companies_id,
                'color_menu' => $request->color_menu,
                'color_sub_menu' => $request->color_sub_menu,
                'color_header' => $request->color_header,
                'color_footer' => $request->color_footer,
                'color_text' => $request->color_text,
                'logo' => $directorio,
            ]);
        } else {
            $customizer->update([
                'companies_id' => $request->companies_id,
                'color_menu' => $request->color_menu,
                'color_sub_menu' => $request->color_sub_menu,
                'color_header' => $request->color_header,
                'color_footer' => $request->color_footer,
                'color_text' => $request->color_text,
            ]);
        }

        return Redirect::route('customizers.index')->with('message', 'Personalización actualizada');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customizer  $customizer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customizer = Customizer::find($id);
        $customizer->delete();
        $sd = $customizer->companies_id;
        $destino = 'img/empresa' . $sd  . '/';
        if (file_exists($destino)) {
            if (file_exists($destino)) {
                $files = glob($destino . '/*'); //obtenemos todos los nombres de los ficheros
                foreach ($files as $file) {
                    if (is_file($file))
                        unlink($file); //elimino el fichero
                }
                rmdir($destino);
            }
        }
        return Redirect::route('customizers.index')->with('message', 'Personalización eliminada');
    }
}
