<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        return view('admin.faqs.index', ['faqs' => Faq::orderBy('sort')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.faqs.form', ['faq' => new Faq(['is_published' => true, 'sort' => Faq::max('sort') + 1])]);
    }

    public function store(Request $request)
    {
        Faq::create($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('status', 'Question added.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.form', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('status', 'Question saved.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return back()->with('status', 'Question deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:3000'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $data['sort'] = (int) ($data['sort'] ?? 0);

        return $data;
    }
}
