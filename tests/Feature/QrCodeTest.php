<?php

namespace Tests\Feature;

use App\Models\QrCode;
use App\Models\Setting;
use App\Models\User;
use chillerlan\QRCode\QRCode as QrReader;
use chillerlan\QRCode\QROptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed();
    }

    public function test_follow_page_shows_social_buttons_and_stays_hidden(): void
    {
        $this->get('/follow')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('Follow us on Instagram')
            ->assertSee(Setting::get('instagram'), false)
            ->assertSee(Setting::get('tiktok'), false)
            ->assertSee('noindex', false);

        // Hidden: not linked from the site or listed in the sitemap
        $this->get('/')->assertDontSee(route('follow'), false);
        $this->get('/sitemap.xml')->assertDontSee(route('follow'), false);
    }

    public function test_follow_page_skips_networks_without_a_link(): void
    {
        Setting::put(['tiktok' => '']);

        $this->get('/follow')->assertOk()->assertDontSee('Watch our bakes');
    }

    public function test_guests_cannot_manage_qr_codes(): void
    {
        $this->get('/admin/qr-codes')->assertRedirect('/admin/login');
        $this->post('/admin/qr-codes', ['label' => 'x', 'url' => 'https://example.com'])->assertRedirect('/admin/login');
    }

    public function test_admin_creates_a_qr_code_that_scans_to_the_link(): void
    {
        $this->actingAs(User::first());
        $this->get('/admin/qr-codes')->assertOk()->assertSee('New QR code');

        $this->post('/admin/qr-codes', ['label' => 'Cake box sticker', 'url' => 'https://sabspastries.com/follow'])
            ->assertRedirect('/admin/qr-codes');

        $qr = QrCode::sole();
        Storage::disk('public')->assertExists([$qr->png_path, $qr->svg_path]);

        // The saved PNG (with the logo over the centre) still decodes to the link
        $decoded = (new QrReader(new QROptions(['readerUseImagickIfAvailable' => false])))
            ->readFromBlob(Storage::disk('public')->get($qr->png_path));
        $this->assertSame('https://sabspastries.com/follow', (string) $decoded);

        $this->get('/admin/qr-codes')->assertSee('Cake box sticker');
    }

    public function test_admin_downloads_png_and_svg(): void
    {
        $this->actingAs(User::first());
        $qr = QrCode::generate('Follow us', 'https://sabspastries.com/follow');

        $this->get(route('admin.qr-codes.download', [$qr, 'png']))
            ->assertOk()->assertDownload('sabs-pastries-follow-us.png');
        $this->get(route('admin.qr-codes.download', [$qr, 'svg']))
            ->assertOk()->assertDownload('sabs-pastries-follow-us.svg');
        $this->get('/admin/qr-codes/'.$qr->id.'/download/pdf')->assertNotFound();
    }

    public function test_link_must_be_a_full_web_address(): void
    {
        $this->actingAs(User::first());

        $this->post('/admin/qr-codes', ['label' => 'Bad', 'url' => 'sabspastries'])->assertSessionHasErrors('url');
        $this->post('/admin/qr-codes', ['label' => 'Bad', 'url' => 'javascript:alert(1)'])->assertSessionHasErrors('url');
        $this->assertSame(0, QrCode::count());
    }

    public function test_deleting_a_qr_code_removes_its_files(): void
    {
        $this->actingAs(User::first());
        $qr = QrCode::generate('Old flyer', 'https://sabspastries.com/follow');

        $this->delete(route('admin.qr-codes.destroy', $qr))->assertRedirect();

        $this->assertModelMissing($qr);
        Storage::disk('public')->assertMissing([$qr->png_path, $qr->svg_path]);
    }
}
