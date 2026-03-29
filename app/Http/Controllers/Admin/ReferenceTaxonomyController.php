<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Severity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReferenceTaxonomyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ReferenceData/Index', [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'severities' => Severity::query()->orderBy('level')->get(['id', 'name', 'level']),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        $nextId = (int) (Category::withTrashed()->max('id') ?? 0) + 1;

        $category = new Category;
        $category->id = $nextId;
        $category->name = $validated['name'];
        $category->save();

        return redirect()->route('admin.reference-data.index')->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);

        $category->update(['name' => $validated['name']]);

        return redirect()->route('admin.reference-data.index')->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->reports()->exists()) {
            return back()->withErrors(['error' => 'This category is assigned to one or more incidents and cannot be deleted.']);
        }

        $category->delete();

        return redirect()->route('admin.reference-data.index')->with('success', 'Category deleted.');
    }

    public function storeSeverity(Request $request): RedirectResponse
    {
        $this->authorize('create', Severity::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:severities,name'],
            'level' => ['required', 'integer', 'min:0', 'max:999999'],
        ]);

        $nextId = (int) (Severity::withTrashed()->max('id') ?? 0) + 1;

        $severity = new Severity;
        $severity->id = $nextId;
        $severity->name = $validated['name'];
        $severity->level = $validated['level'];
        $severity->save();

        return redirect()->route('admin.reference-data.index')->with('success', 'Severity level created.');
    }

    public function updateSeverity(Request $request, Severity $severity): RedirectResponse
    {
        $this->authorize('update', $severity);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('severities', 'name')->ignore($severity->id)],
            'level' => ['required', 'integer', 'min:0', 'max:999999'],
        ]);

        $severity->update([
            'name' => $validated['name'],
            'level' => $validated['level'],
        ]);

        return redirect()->route('admin.reference-data.index')->with('success', 'Severity level updated.');
    }

    public function destroySeverity(Severity $severity): RedirectResponse
    {
        $this->authorize('delete', $severity);

        if ($severity->reports()->exists()) {
            return back()->withErrors(['error' => 'This severity is assigned to one or more incidents and cannot be deleted.']);
        }

        $severity->delete();

        return redirect()->route('admin.reference-data.index')->with('success', 'Severity level deleted.');
    }
}
