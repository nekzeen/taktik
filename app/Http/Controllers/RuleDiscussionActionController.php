<?php

namespace App\Http\Controllers;

use App\Models\RuleDiscussion;
use App\Models\RuleDiscussionCategory;
use App\Models\RuleDiscussionReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RuleDiscussionActionController extends Controller
{
    public function create()
    {
        $categories = RuleDiscussionCategory::where('is_active', true)->orderBy('order')->get();
        return view('rule-discussions.create', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:rule_discussion_categories,id',
            'description' => 'required|string|max:5000',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('rule-discussions', 'public');
        }

        $discussion = RuleDiscussion::create([
            'user_id' => auth()->id(),
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
        ]);

        return redirect()->route('rule-discussions.show', $discussion)
            ->with('success', 'Votre question a été postée avec succès !');
    }

    public function storeReply(Request $request, RuleDiscussion $discussion)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        RuleDiscussionReply::create([
            'discussion_id' => $discussion->id,
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Votre réponse a été soumise et est en attente de modération.');
    }

    public function edit(RuleDiscussion $discussion)
    {
        if ($discussion->user_id !== auth()->id() && !auth()->user()->hasRole(['moderator', 'admin', 'super-admin'])) {
            abort(403);
        }

        $categories = RuleDiscussionCategory::where('is_active', true)->orderBy('order')->get();
        return view('rule-discussions.edit', ['discussion' => $discussion, 'categories' => $categories]);
    }

    public function update(Request $request, RuleDiscussion $discussion)
    {
        if ($discussion->user_id !== auth()->id() && !auth()->user()->hasRole(['moderator', 'admin', 'super-admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:rule_discussion_categories,id',
            'description' => 'required|string|max:5000',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            if ($discussion->image_path) {
                Storage::disk('public')->delete($discussion->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('rule-discussions', 'public');
        }

        $discussion->update($validated);

        return redirect()->route('rule-discussions.show', $discussion)
            ->with('success', 'Votre question a été mise à jour avec succès !');
    }

    public function destroy(RuleDiscussion $discussion)
    {
        if ($discussion->user_id !== auth()->id() && !auth()->user()->hasRole(['moderator', 'admin', 'super-admin'])) {
            abort(403);
        }

        if ($discussion->image_path) {
            Storage::disk('public')->delete($discussion->image_path);
        }

        $discussion->delete();

        return redirect()->route('rule-discussions.index')
            ->with('success', 'Votre question a été supprimée avec succès !');
    }
}
