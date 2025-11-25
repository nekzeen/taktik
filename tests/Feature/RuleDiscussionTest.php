<?php

namespace Tests\Feature;

use App\Models\RuleDiscussion;
use App\Models\RuleDiscussionCategory;
use App\Models\RuleDiscussionTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleDiscussionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $category;
    protected $tags;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category = RuleDiscussionCategory::factory()->create();
        $this->tags = RuleDiscussionTag::factory(3)->create();
    }

    public function test_can_view_rule_discussions_index()
    {
        $response = $this->get('/rule-discussions');
        $response->assertStatus(200);
        $response->assertViewIs('rule-discussions.index');
    }

    public function test_authenticated_user_can_create_discussion()
    {
        $response = $this->actingAs($this->user)
            ->get('/rule-discussions/create');

        $response->assertStatus(200);
        $response->assertViewIs('rule-discussions.create');
    }

    public function test_unauthenticated_user_cannot_create_discussion()
    {
        $response = $this->get('/rule-discussions/create');
        $response->assertRedirect('/login');
    }

    public function test_can_store_discussion()
    {
        $data = [
            'title' => 'Test Discussion',
            'category_id' => $this->category->id,
            'description' => 'This is a test discussion about rules',
            'tags' => [$this->tags[0]->id, $this->tags[1]->id],
        ];

        $response = $this->actingAs($this->user)
            ->post('/rule-discussions', $data);

        $this->assertDatabaseHas('rule_discussions', [
            'title' => 'Test Discussion',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $discussion = RuleDiscussion::where('title', 'Test Discussion')->first();
        $this->assertEquals(2, $discussion->tags()->count());
    }

    public function test_can_view_discussion()
    {
        $discussion = RuleDiscussion::factory()
            ->for($this->user)
            ->for($this->category)
            ->create();

        $response = $this->get("/rule-discussions/{$discussion->id}");
        $response->assertStatus(200);
        $response->assertViewIs('rule-discussions.show');
        $response->assertViewHas('discussion', $discussion);
    }

    public function test_can_reply_to_discussion()
    {
        $discussion = RuleDiscussion::factory()
            ->for($this->user)
            ->for($this->category)
            ->create();

        $replyData = [
            'content' => 'This is a test reply',
        ];

        $response = $this->actingAs($this->user)
            ->post("/rule-discussions/{$discussion->id}/replies", $replyData);

        $this->assertDatabaseHas('rule_discussion_replies', [
            'discussion_id' => $discussion->id,
            'user_id' => $this->user->id,
            'content' => 'This is a test reply',
        ]);
    }

    public function test_can_vote_on_discussion()
    {
        $discussion = RuleDiscussion::factory()
            ->for($this->user)
            ->for($this->category)
            ->create();

        $response = $this->actingAs($this->user)
            ->post("/rule-discussions/{$discussion->id}/vote", [
                'vote_type' => 'useful',
            ]);

        $this->assertDatabaseHas('rule_discussion_votes', [
            'discussion_id' => $discussion->id,
            'user_id' => $this->user->id,
            'vote_type' => 'useful',
        ]);
    }

    public function test_can_archive_own_discussion()
    {
        $discussion = RuleDiscussion::factory()
            ->for($this->user)
            ->for($this->category)
            ->create();

        $response = $this->actingAs($this->user)
            ->post("/rule-discussions/{$discussion->id}/archive");

        $discussion->refresh();
        $this->assertEquals('archived', $discussion->status);
        $this->assertNotNull($discussion->archived_at);
    }

    public function test_cannot_archive_others_discussion()
    {
        $otherUser = User::factory()->create();
        $discussion = RuleDiscussion::factory()
            ->for($otherUser)
            ->for($this->category)
            ->create();

        $response = $this->actingAs($this->user)
            ->post("/rule-discussions/{$discussion->id}/archive");

        $response->assertStatus(403);
    }

    public function test_can_search_discussions()
    {
        RuleDiscussion::factory()
            ->for($this->user)
            ->for($this->category)
            ->create(['title' => 'Deployment Rules']);

        RuleDiscussion::factory()
            ->for($this->user)
            ->for($this->category)
            ->create(['title' => 'Scoring System']);

        $response = $this->get('/api/rule-discussions/search?q=Deployment');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'results');
    }

    public function test_archived_discussions_not_in_main_list()
    {
        $discussion = RuleDiscussion::factory()
            ->for($this->user)
            ->for($this->category)
            ->create(['status' => 'archived']);

        $response = $this->get('/rule-discussions');
        $response->assertDontSee($discussion->title);
    }

    public function test_archived_discussions_in_archived_list()
    {
        $discussion = RuleDiscussion::factory()
            ->for($this->user)
            ->for($this->category)
            ->create(['status' => 'archived']);

        $response = $this->get('/rule-discussions/archived');
        $response->assertSee($discussion->title);
    }
}
