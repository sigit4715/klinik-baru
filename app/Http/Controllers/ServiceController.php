<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        $per_page = session('pagination_per_page') ?? 20;
        $services = Service::orderBy('id', 'DESC')->paginate($per_page);
        $title = "Daftar Layanan";
        $description = "Kelola layanan klinik";

        return view('pages.clinic.services.index', compact('title', 'description', 'services'));
    }

    public function create()
    {
        $title = "Tambah Layanan Baru";
        $description = "Form tambah layanan klinik";
        $code = Service::generateCode();
        return view('pages.clinic.services.create', compact('title', 'description', 'code'));
    }

    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.services.create', app()->getLocale())
                ->withErrors($validators)->withInput();
        }

        $service = new Service();
        $service->code = Service::generateCode();
        $service->name = $request->name;
        $service->description = $request->description;
        $service->price = $request->price;
        $service->status = $request->status ?? 'active';
        $service->save();

        return redirect()->route('clinic.services.index', app()->getLocale())
            ->with('success', 'Layanan berhasil ditambahkan!');
    }

    public function edit($language, $id)
    {
        $service = Service::findOrFail($id);
        $title = "Edit Layanan";
        $description = "Ubah data layanan";

        return view('pages.clinic.services.edit', compact('title', 'description', 'service'));
    }

    public function update(Request $request, $language, $id)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.services.edit', [app()->getLocale(), $id])
                ->withErrors($validators)->withInput();
        }

        $service = Service::findOrFail($id);
        $service->name = $request->name;
        $service->description = $request->description;
        $service->price = $request->price;
        $service->status = $request->status ?? 'active';
        $service->save();

        return redirect()->route('clinic.services.index', app()->getLocale())
            ->with('success', 'Layanan berhasil diperbarui!');
    }

    public function destroy($language, $id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        return redirect()->route('clinic.services.index', app()->getLocale())
            ->with('success', 'Layanan berhasil dihapus!');
    }

    public function getActive()
    {
        $services = Service::active()->get();
        return response()->json($services);
    }
}
