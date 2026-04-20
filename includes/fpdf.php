<?php
declare(strict_types=1);

class FPDF
{
    private array $lines = [];
    private string $font = 'Helvetica';
    private int $fontSize = 12;

    public function AddPage(): void
    {
        $this->lines = [];
    }

    public function SetFont(string $family, string $style = '', int $size = 12): void
    {
        $this->font = $family;
        $this->fontSize = $size;
    }

    public function Cell(float $w, float $h, string $txt = '', int $border = 0, int $ln = 0, string $align = ''): void
    {
        $this->lines[] = $txt;
        if ($ln > 0) {
            $this->Ln();
        }
    }

    public function Ln(float $h = 0): void
    {
        $this->lines[] = "\n";
    }

    public function Output(string $dest = 'I', string $name = 'report.pdf'): void
    {
        $content = implode("\n", $this->lines);
        $pdf = $this->buildPdf($content);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $name . '"');
        echo $pdf;
    }

    private function buildPdf(string $text): string
    {
        $safeText = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $stream = "BT /F1 {$this->fontSize} Tf 50 780 Td ({$safeText}) Tj ET";

        $obj1 = "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n";
        $obj2 = "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n";
        $obj3 = "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>endobj\n";
        $obj4 = "4 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n";
        $obj5 = "5 0 obj<< /Length " . strlen($stream) . " >>stream\n{$stream}\nendstream endobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ([$obj1, $obj2, $obj3, $obj4, $obj5] as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj;
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= str_pad((string)$offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";
        return $pdf;
    }
}
