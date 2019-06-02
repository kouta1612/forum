<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Activity;
use App\Thread;
use App\Rules\Recaptcha;

class CreateThreadsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->singleton(Recaptcha::class, function () {
            return \Mockery::mock(Recaptcha::class, function ($m) {
                $m->shouldReceive('passes')->andReturn(true);
            });
        });
    }

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
        $user = factory('App\User')->state('unconfirmed')->create();

        $this->signIn($user);

        $thread = make('App\Thread');

        return $this->post('/threads', $thread->toArray())
            ->assertRedirect('/threads')
            ->assertSessionHas('flash', 'You must first confirm your email address');
    }

    /** @test */
    public function a_user_can_create_new_forum_threads()
    {
        $response = $this->publishedThread(['title' => 'Some title', 'body' => 'Some body']);

        $this->get($response->headers->get('location'))
            ->assertSee('Some title')
            ->assertSee('Some body');
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
    public function a_threads_require_a_rechaptcha_varification()
    {
        unset(app()[Recaptcha::class]);

        $this->publishedThread(['g-recaptcha-response' => 'test'])
            ->assertSessionHasErrors('g-recaptcha-response');
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
    public function a_thread_requires_a_unique_slug()
    {
        $this->signIn();

        $thread = create('App\Thread', ['title' => 'foo bar']);

        $this->assertEquals($thread->fresh()->slug, 'foo-bar');

        $thread = $this->postJson('/threads', $thread->toArray() + ['g-recaptcha-response' => 'token'])->json();

        $this->assertEquals("foo-bar-{$thread['id']}", $thread['slug']);
    }

    /** @test */
    public function a_thread_with_a_title_that_ends_in_a_number_should_generate_the_proper_slug()
    {
        $this->signIn();

        $thread = create('App\Thread', ['title' => 'Some Title 24']);

        $thread = $this->postJson('/threads', $thread->toArray() + ['g-recaptcha-response' => 'token'])->json();

        $this->assertEquals("some-title-24-{$thread['id']}", $thread['slug']);
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

        return $this->post('/threads', $thread->toArray() + ['g-recaptcha-response' => 'token']);
    }
}
