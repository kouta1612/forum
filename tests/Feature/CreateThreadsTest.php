<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Activity;

class CreateThreadsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_may_not_create_threads()
    {
        $this->withExceptionHandling();

        $this->get('/threads/create')
            ->assertRedirect('/login');

        $this->post('/threads')
            ->assertRedirect('/login');
    }

    /** @test */
    public function new_users_must_first_confirm_their_email_address_before_creating_threads()
    {
        $this->publishedThread()
            ->assertRedirect('/threads')
            ->assertSessionHas('flash', 'You must first confirm your email address');
    }

    /** @test */
    public function a_user_can_create_new_forum_threads()
    {
        $this->signIn();

        $thread = make('App\Thread');

        $response = $this->post('/threads', $thread->toArray());

        $this->get($response->headers->get('location'))
            ->assertSee($thread->title)
            ->assertSee($thread->body);
    }

    /** @test */
    public function a_threads_require_a_title()
    {
        $this->publishedThread(['title' => null])
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_threads_require_a_body()
    {
        $this->publishedThread(['body' => null])
            ->assertSessionHasErrors('body');
    }

    /** @test */
    public function a_threads_require_a_valid_channel()
    {
        factory('App\Channel', 2)->create();

        $this->publishedThread(['channel_id' => null])
            ->assertSessionHasErrors('channel_id');

        $this->publishedThread(['channel_id' => 999])
            ->assertSessionHasErrors('channel_id');
    }

    /** @test */
    public function unauthrized_users_may_not_delete_threads()
    {
        $this->withExceptionHandling();

        $thread = create('App\Thread');

        $this->delete($thread->path())->assertRedirect('/login');

        $this->signIn();
        $this->delete($thread->path())->assertStatus(403);
    }

    /** @test */
    public function authorized_users_can_delete_threads()
    {
        $this->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id()]);
        $reply = create('App\Reply', ['thread_id' => $thread->id]);

        $response = $this->json('DELETE', $thread->path());

        $response->assertStatus(204);

        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
        $this->assertEquals(0, Activity::count());
    }

    protected function publishedThread($overrides = [])
    {
        $this->withExceptionHandling()->signIn();

        $thread = make('App\Thread', $overrides);

        return $this->post('/threads', $thread->toArray());
    }
}
