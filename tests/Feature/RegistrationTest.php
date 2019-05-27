<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use App\Mail\PleaseConfirmYourEmail;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_confirmation_email_is_sent_upon_registration()
    {
        Mail::fake();

        event(new Registered(create('App\User')));

        Mail::assertSent(PleaseConfirmYourEmail::class);
    }

    /** @test */
    public function user_can_fully_confirm_their_email_address()
    {
        $this->post('/register', [
            'name' => 'john',
            'email' => 'john@gmail.com',
            'password' => 'foobar',
            'password_confirmation' => 'foobar'
        ]);

        $user = User::whereName('john')->first();

        $this->assertFalse($user->confirmed);
    }
}
