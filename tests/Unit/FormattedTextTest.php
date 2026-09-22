<?php

namespace Tests\Unit;

use App\Support\FormattedText;
use PHPUnit\Framework\TestCase;

class FormattedTextTest extends TestCase
{
    public function test_plain_text_keeps_line_breaks_and_escapes_html(): void
    {
        $html = FormattedText::html("Halo kelas\nBaris dua\n\n<script>alert(1)</script>");

        $this->assertSame(
            '<p>Halo kelas<br>Baris dua</p><p>&lt;script&gt;alert(1)&lt;/script&gt;</p>',
            $html,
        );
    }

    public function test_bold_italic_and_links_render_safely(): void
    {
        $html = FormattedText::html("Ini *penting* dan _miring_.\nLihat [jadwal](https://example.com/kelas).");

        $this->assertStringContainsString('<strong>penting</strong>', $html);
        $this->assertStringContainsString('<em>miring</em>', $html);
        $this->assertStringContainsString('href="https://example.com/kelas"', $html);
        $this->assertStringContainsString('>jadwal</a>', $html);
        $unsafe = FormattedText::html('[klik](javascript:alert(1))');
        $this->assertStringNotContainsString('href="javascript:', $unsafe);
        $this->assertStringContainsString('javascript:alert(1)', $unsafe);
    }

    public function test_a_bare_address_becomes_a_link(): void
    {
        $html = FormattedText::html('Buka https://01sise001.online/hadir.');

        $this->assertStringContainsString('href="https://01sise001.online/hadir"', $html);
        $this->assertStringContainsString('hadir</a>.', $html);
    }

    public function test_dash_lines_become_a_list(): void
    {
        $html = FormattedText::html("- bawa laptop\n- *datang jam 7*");

        $this->assertStringContainsString('<ul><li>bawa laptop</li><li><strong>datang jam 7</strong></li></ul>', $html);
    }

    public function test_whatsapp_copy_uses_stars_and_plain_links(): void
    {
        $text = FormattedText::toWhatsapp("**Penting**\n[jadwal](https://example.com)");

        $this->assertSame("*Penting*\njadwal (https://example.com)", $text);
    }
}
