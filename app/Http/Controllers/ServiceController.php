<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Poli;
use App\Models\Service;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $services = $query->orderBy('id', 'DESC')->paginate(10)->withQueryString();
        $title = "Daftar Layanan";
        $description = "Kelola layanan klinik";

        return view('pages.clinic.services.index', compact('title', 'description', 'services'));
    }

    public function create()
    {
        $title = "Tambah Layanan Baru";
        $description = "Form tambah layanan klinik";
        $code = Service::generateCode();
        $polis = Poli::active()->orderBy('name')->get();
        return view('pages.clinic.services.create', compact('title', 'description', 'code', 'polis'));
    }

    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => ['required', Rule::in(array_keys(Service::CATEGORIES))],
            'poli_id' => ['nullable', 'integer', Rule::exists('polis', 'id')->whereNull('deleted_at')],
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.services.create', app()->getLocale())
                ->withErrors($validators)->withInput();
        }

        $service = new Service();
        $service->code = Service::generateCode();
        $service->name = $request->name;
        $service->category = $request->category;
        $service->poli_id = $request->poli_id ?: null;
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

        $polis = Poli::orderBy('name')->get();

        return view('pages.clinic.services.edit', compact('title', 'description', 'service', 'polis'));
    }

    public function update(Request $request, $language, $id)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => ['required', Rule::in(array_keys(Service::CATEGORIES))],
            'poli_id' => ['nullable', 'integer', Rule::exists('polis', 'id')->whereNull('deleted_at')],
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.services.edit', [app()->getLocale(), $id])
                ->withErrors($validators)->withInput();
        }

        $service = Service::findOrFail($id);
        $service->name = $request->name;
        $service->category = $request->category;
        $service->poli_id = $request->poli_id ?: null;
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
        $service->delete(); // soft delete: moved to Arsip
        return redirect()->route('clinic.services.index', app()->getLocale())
            ->with('success', "Layanan {$service->name} dipindahkan ke Arsip.");
    }

    public function getActive()
    {
        $services = Service::active()->get();
        return response()->json($services);
    }
}
