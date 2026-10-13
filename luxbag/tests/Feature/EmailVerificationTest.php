<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Mockery\MockInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
    }

    public function test_registration_sends_a_real_notification_with_html_text_and_a_signed_link(): void
    {
        Setting::create(['key' => 'site_name', 'value' => 'Bloom & Bold', 'group' => 'general']);

        $this->post(route('register'), [
            'name' => 'Khách mua túi',
            'email' => 'buyer@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ])->assertRedirect(route('verification.notice'))->assertSessionHas('success');

        $user = User::where('email', 'buyer@example.com')->firstOrFail();
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertAuthenticatedAs($user);

        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $email = $messages->first()->getOriginalMessage();
        $this->assertSame('buyer@example.com', $email->getTo()[0]->getAddress());
        $this->assertSame('Xác thực tài khoản mua hàng - Bloom & Bold', $email->getSubject());
        $this->assertSame('Bloom & Bold', $email->getFrom()[0]->getName());
        $this->assertStringContainsString('Khách mua túi', $email->getHtmlBody());
        $this->assertStringContainsString('60 phút', $email->getTextBody());

        preg_match('~https?://[^\s]+/xac-thuc-email/[^\s]+~', $email->getTextBody(), $matches);
        $this->assertNotEmpty($matches);
        $this->assertTrue(URL::hasValidSignature(Request::create($matches[0])));
        $this->get($matches[0])->assertRedirect(route('home'));
        $this->assertSame('active', $user->fresh()->status);
    }

    public function test_verification_notice_displays_the_email_and_resend_form(): void
    {
        $user = $this->pendingUser();

        $this->actingAs($user)->get(route('verification.notice'))
            ->assertOk()
            ->assertSee($user->email)
            ->assertSee('Gửi lại email xác thực')
            ->assertSee(route('verification.send'), false);
    }

    public function test_registration_on_bloombold_https_domain_sends_a_valid_link_and_a_secure_cookie(): void
    {
        config(['session.secure' => true]);

        $response = $this->post('https://bloombold.shop/dang-ky', [
            'name' => 'Buyer', 'email' => 'buyer@example.com',
            'password' => 'Password1', 'password_confirmation' => 'Password1',
        ]);
        $response->assertRedirect('https://bloombold.shop/xac-thuc-email');

        $cookies = collect($response->headers->getCookies());
        $sessionCookie = $cookies->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));
        $this->assertNotNull($sessionCookie);
        $this->assertTrue($sessionCookie->isSecure());

        $email = Mail::mailer()->getSymfonyTransport()->messages()->first()->getOriginalMessage();
        preg_match('~https://bloombold\\.shop/xac-thuc-email/[^\\s]+~', $email->getTextBody(), $matches);
        $this->assertNotEmpty($matches);
        $this->assertTrue(URL::hasValidSignature(Request::create($matches[0])));
        $this->get($matches[0])->assertRedirect('https://bloombold.shop');
        $this->assertSame('active', User::where('email', 'buyer@example.com')->firstOrFail()->status);
    }

    public function test_registration_preserves_the_guest_cart_when_regenerating_the_session(): void
    {
        Notification::fake();
        $category = Category::create(['name' => 'Bags', 'slug' => 'bags', 'status' => 'active']);
        $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id, 'brand_id' => $brand->id,
            'name' => 'Test Bag', 'slug' => 'test-bag', 'sku' => 'VERIFY-CART-1',
            'price' => 1000000, 'stock' => 10, 'status' => 'active',
        ]);
        $this->withSession(['checkout_pending' => true]);
        $oldSessionId = session()->getId();
        $this->withCookie(config('session.cookie'), $oldSessionId);
        $cart = Cart::create([
            'session_id' => $oldSessionId, 'product_id' => $product->id, 'quantity' => 2,
        ]);

        $this->post(route('register'), [
            'name' => 'Buyer', 'email' => 'buyer@example.com',
            'password' => 'Password1', 'password_confirmation' => 'Password1',
        ])->assertRedirect(route('verification.notice'))->assertSessionHas('checkout_pending', true);

        $user = User::where('email', 'buyer@example.com')->firstOrFail();
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id, 'user_id' => $user->id, 'session_id' => null, 'quantity' => 2,
        ]);
        $this->assertNotSame($oldSessionId, session()->getId());
    }

    public function test_valid_link_activates_the_account_and_dispatches_verified_once(): void
    {
        Event::fake([Verified::class]);
        $user = $this->pendingUser(['email_verify_token' => 'legacy-token']);
        $url = $this->verificationUrl($user);

        $this->actingAs($user)->get($url)->assertRedirect(route('home'))->assertSessionHas('success');
        $user->refresh();
        $this->assertSame('active', $user->status);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->email_verify_token);
        $this->assertTrue($user->hasVerifiedEmail());

        $this->get($url)->assertRedirect(route('home'));
        Event::assertDispatchedTimes(Verified::class, 1);
        $this->get(route('orders.checkout'))->assertRedirect(route('cart.index'));
    }

    public function test_expired_link_does_not_activate_the_account(): void
    {
        $user = $this->pendingUser();
        $url = $this->verificationUrl($user, -1);

        $this->actingAs($user)->get($url)
            ->assertRedirect(route('verification.notice'))->assertSessionHas('error');
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame('unverified', $user->fresh()->status);
    }

    public function test_tampered_signature_does_not_activate_the_account(): void
    {
        $user = $this->pendingUser();
        $url = str_replace('signature=', 'signature=invalid', $this->verificationUrl($user));

        $this->actingAs($user)->get($url)
            ->assertRedirect(route('verification.notice'))->assertSessionHas('error');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_link_cannot_verify_another_authenticated_user(): void
    {
        $owner = $this->pendingUser();
        $other = $this->pendingUser();

        $this->actingAs($other)->get($this->verificationUrl($owner))->assertForbidden();
        $this->assertNull($owner->fresh()->email_verified_at);
        $this->assertNull($other->fresh()->email_verified_at);
    }

    public function test_link_with_the_wrong_email_hash_is_rejected(): void
    {
        $user = $this->pendingUser();
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->getKey(),
            'hash' => sha1('wrong@example.com'),
        ]);

        $this->actingAs($user)->get($url)->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_opening_email_in_another_browser_preserves_the_link_through_login(): void
    {
        $user = $this->pendingUser(['password' => 'Password1']);
        $url = $this->verificationUrl($user);

        $this->get($url)->assertRedirect(route('login'));
        $this->post(route('login'), ['email' => $user->email, 'password' => 'Password1'])
            ->assertRedirect($url);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->get($url)->assertRedirect(route('home'));
        $this->assertSame('active', $user->fresh()->status);
    }

    public function test_unverified_accounts_cannot_access_checkout_orders_profile_or_admin(): void
    {
        $user = $this->pendingUser();
        $user->assignRole('admin');
        $this->actingAs($user);

        $this->get(route('orders.checkout'))->assertRedirect(route('verification.notice'));
        $this->post(route('orders.store'), [])->assertRedirect(route('verification.notice'));
        $this->get(route('profile.index'))->assertRedirect(route('verification.notice'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('verification.notice'));
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_active_account_with_no_verified_email_still_requires_verification(): void
    {
        $user = $this->pendingUser(['status' => 'active', 'password' => 'Password1']);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'Password1'])
            ->assertRedirect(route('verification.notice'));
        $this->get(route('orders.checkout'))->assertRedirect(route('verification.notice'));
    }

    public function test_banned_account_cannot_be_activated_by_verification_link(): void
    {
        $user = $this->pendingUser(['status' => 'banned']);

        $this->actingAs($user)->get($this->verificationUrl($user))
            ->assertRedirect(route('login'))->assertSessionHas('error');
        $this->assertSame('banned', $user->fresh()->status);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertGuest();
    }

    public function test_resend_sends_email_and_enforces_one_minute_between_requests(): void
    {
        Notification::fake();
        $user = $this->pendingUser();
        $this->actingAs($user);

        $this->post(route('verification.send'))->assertRedirect(route('verification.notice'))
            ->assertSessionHas('success');
        $this->post(route('verification.send'))->assertSessionHas('warning');
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);

        $this->travel(61)->seconds();
        $this->post(route('verification.send'))->assertSessionHas('success');
        Notification::assertSentToTimes($user, VerifyEmail::class, 2);
    }

    public function test_registration_also_starts_the_resend_cooldown(): void
    {
        Notification::fake();
        $this->post(route('register'), [
            'name' => 'Buyer', 'email' => 'buyer@example.com',
            'password' => 'Password1', 'password_confirmation' => 'Password1',
        ])->assertRedirect(route('verification.notice'));

        $this->post(route('verification.send'))->assertSessionHas('warning');
        $user = User::where('email', 'buyer@example.com')->firstOrFail();
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    public function test_verified_accounts_are_not_sent_more_verification_emails(): void
    {
        Notification::fake();
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($user)->post(route('verification.send'))->assertRedirect(route('home'));
        $this->get(route('verification.notice'))->assertRedirect(route('home'));
        Notification::assertNothingSent();
    }

    public function test_guests_cannot_access_the_notice_or_resend_email(): void
    {
        Notification::fake();
        $this->get(route('verification.notice'))->assertRedirect(route('login'));
        $this->post(route('verification.send'))->assertRedirect(route('login'));
        Notification::assertNothingSent();
    }

    public function test_smtp_failure_keeps_registration_pending_and_logs_no_credentials(): void
    {
        Log::spy();
        $this->mock(Dispatcher::class, function (MockInterface $mock) {
            $mock->shouldReceive('send')->once()
                ->andThrow(new TransportException('Sensitive SMTP details must not be logged'));
        });

        $this->post(route('register'), [
            'name' => 'Buyer', 'email' => 'buyer@example.com',
            'password' => 'Password1', 'password_confirmation' => 'Password1',
        ])->assertRedirect(route('verification.notice'))->assertSessionHas('error');

        $user = User::where('email', 'buyer@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('unverified', $user->status);
        Log::shouldHaveReceived('warning')->once()->with('Unable to send account verification email.', [
            'user_id' => $user->getKey(), 'exception_type' => TransportException::class,
        ]);
        $this->get(route('verification.notice'))->assertOk()->assertSee('chưa gửi được email xác thực');
    }

    public function test_resend_handles_smtp_failure_without_activating_the_account(): void
    {
        $user = $this->pendingUser();
        $this->mock(Dispatcher::class, function (MockInterface $mock) {
            $mock->shouldReceive('send')->once()->andThrow(new TransportException('SMTP unavailable'));
        });

        $this->actingAs($user)->post(route('verification.send'))
            ->assertRedirect(route('verification.notice'))->assertSessionHas('error');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_legacy_unsigned_token_route_can_no_longer_log_in_a_user(): void
    {
        $user = $this->pendingUser(['email_verify_token' => 'old-unsigned-token']);

        $this->get('/xac-thuc-email/old-unsigned-token')->assertNotFound();
        $this->assertGuest();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    private function pendingUser(array $attributes = []): User
    {
        return User::factory()->unverified()->create(array_merge(['status' => 'unverified'], $attributes));
    }

    private function verificationUrl(User $user, int $minutes = 60): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes($minutes), [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);
    }
}
