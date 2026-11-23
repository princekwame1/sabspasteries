<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        // Reviews waiting for approval first, then the published order.
        return view('admin.testimonials.index', ['testimonials' => Testimonial::orderBy('is_published')->orderBy('sort')->latest('id')->get()]);
    }

    /** Approve a review sent from the public review page. */
    public function publish(Testimonial $testimonial)
    {
        // Newest approved review goes first, so it shows on the home page straight away.
        Testimonial::where('is_published', true)->increment('sort');
        $testimonial->update(['is_published' => true, 'sort' => 0]);

        return back()->with('status', 'Review by '.$testimonial->name.' is now on the site.');
    }

    public function create()
    {
        return view('admin.testimonials.form', ['testimonial' => new Testimonial(['rating' => 5, 'is_published' => true, 'sort' => Testimonial::max('sort') + 1])]);
    }

    public function store(Request $request)
    {
        Testimonial::create($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Review added.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.form', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $testimonial->update($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Review saved.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return back()->with('status', 'Review deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:120'],
            'quote' => ['required', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $data['sort'] = (int) ($data['sort'] ?? 0);

        return $data;
    }
}
