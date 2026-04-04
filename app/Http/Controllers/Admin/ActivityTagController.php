<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ActivityTagController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityTag::query()->withCount('products')->orderBy('group_name')->orderBy('name');

        if ($request->filled('q')) {
            $q = trim((string) $request->string('q'));
            $query->where(function ($builder) use ($q) {
                $builder
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('group_name', 'like', "%{$q}%");
            });
        }

        $tags = $query->paginate(15)->withQueryString();

        return view('backend.activity-tags.index', compact('tags'));
    }

    public function create()
    {
        return view('backend.activity-tags.form', ['tag' => new ActivityTag()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateTag($request);
        ActivityTag::create($data);

        return redirect()->route('admin.activity-tags.index')
            ->with('success', 'Tag aktivitas berhasil ditambahkan.');
    }

    public function edit(ActivityTag $activityTag)
    {
        return view('backend.activity-tags.form', ['tag' => $activityTag]);
    }

    public function update(Request $request, ActivityTag $activityTag)
    {
        $data = $this->validateTag($request, $activityTag);
        $activityTag->update($data);

        return redirect()->route('admin.activity-tags.index')
            ->with('success', 'Tag aktivitas berhasil diperbarui.');
    }

    public function destroy(ActivityTag $activityTag)
    {
        if ($activityTag->products()->exists()) {
            return back()->with('error', 'Tag aktivitas tidak bisa dihapus karena masih dipakai produk.');
        }

        $activityTag->delete();

        return back()->with('success', 'Tag aktivitas berhasil dihapus.');
    }

    private function validateTag(Request $request, ?ActivityTag $tag = null): array
    {
        $request->merge([
            'slug' => $request->filled('slug')
                ? Str::slug((string) $request->input('slug'))
                : Str::slug((string) $request->input('name')),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('activity_tags', 'name')->ignore($tag?->id)],
            'slug' => ['required', 'string', 'max:100', Rule::unique('activity_tags', 'slug')->ignore($tag?->id)],
            'group_name' => ['required', Rule::in(['audience', 'difficulty', 'facility', 'theme'])],
        ]);
    }
}
