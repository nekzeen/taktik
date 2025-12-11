<?php

namespace App\Http\Controllers;

use App\Models\RuleDiscussion;
use App\Models\RuleDiscussionCategory;
use Illuminate\Http\Request;

class RuleDiscussionController extends Controller
{
    public function index(Request $request)
    {
        $categories = RuleDiscussionCategory::where('is_active', true)->orderBy('order')->get();
        
        $query = RuleDiscussion::with(['user', 'category', 'approvedReplies'])
            ->where('status', '!=', 'archived');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Sort
        $sort = $request->input('sort', 'latest');
        if ($sort === 'popular') {
            $query->withCount('approvedReplies')->orderBy('approved_replies_count', 'desc');
        } else {
            $query->latest();
        }

        $discussions = $query->paginate(20);

        return view('rule-discussions.index', [
            'categories' => $categories,
            'discussions' => $discussions,
            'search' => $request->input('search'),
            'selectedCategory' => $request->input('category'),
            'selectedStatus' => $request->input('status'),
            'sort' => $sort,
        ]);
    }

    public function show(RuleDiscussion $discussion)
    {
        $discussion->load(['user', 'category', 'approvedReplies.user']);

        return view('rule-discussions.show', [
            'discussion' => $discussion,
        ]);
    }
}
à