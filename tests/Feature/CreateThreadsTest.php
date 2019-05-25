<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateThreadsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_may_not_create_threads()
    {
        $this->expectException('Illuminate\Auth\AuthenticationException');

        $thread = make('App\Thread');
        $this->post('/threads', $thread->toArray());
    }

    /** @test */
    public function guest_can_not_see_create_thread_page()
    {
        $this->expectException('Illuminate\Auth\AuthenticationException');

        $this->get('/threads/create')->assertRedirect('/login');
    }

    /** @test */
    public function an_authenticated_user_can_create_new_forum_threads()
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
        $this->expectException('Illuminate\Validation\ValidationException');

        $this->signIn();

        $thread = make('App\Thread', ['title' => null]);

        $this->post('/threads', $thread->toArray())->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_threads_require_a_body()
    {
        $this->expectException('Illuminate\Validation\ValidationException');

        $this->signIn();

        $thread = make('App\Thread', ['body' => null]);

        $this->post('/threads', $thread->toArray())->assertSessionHasErrors('body');
    }

    /** @test */
    public function a_threads_require_a_valid_channel()
    {
        $this->expectException('Illuminate\Validation\ValidationException');

        factory('App\Channel', 2)->create();

        $this->signIn();

        $thread1 = make('App\Thread', ['channel_id' => null]);
        $thread2 = make('App\Thread', ['channel_id' => 2]);

        $this->post('/threads', $thread1->toArray())->assertSessionHasErrors('channel_id');
        $this->post('/threads', $thread2->toArray())->assertSessionHasErrors('channel_id');
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
        $this->assertEquals(0, \App\Activity::count());
    }
}
