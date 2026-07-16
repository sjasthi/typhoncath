<?php

namespace App\Core\PDF;

class SimplePDF
{
    private array $objects = [];
    private array $offsets = [];
    private array $lines = [];

    private int $pageWidth = 612;
    private int $pageHeight = 792;


    public function text(
        float $x,
        float $y,
        string $text,
        int $size = 12,
        bool $bold = false
    ): void {

        $font = $bold ? "/F2" : "/F1";

        $text = $this->escape($text);

        $this->lines[] =
        "BT
        $font {$size} Tf
        {$x} {$y} Td
        ($text) Tj
        ET";
    }


    public function title(string $text): void
    {
        $this->text(
            50,
            740,
            $text,
            24,
            true
        );
    }


    public function heading(string $text, float $y): void
    {

        // draw line underneath heading
        $this->line(
            50,
            $y - 8,
            560,
            $y - 8
        );


        // place heading above line
        $this->lines[] =
            "BT\n" .
            "/F2 14 Tf\n" .
            sprintf("50 %.2f Td\n", $y) .
            "(" . $this->escape($text) . ") Tj\n" .
            "ET";
    }


    public function line(
        float $x1,
        float $y1,
        float $x2,
        float $y2
    ): void {

        $this->lines[] =
        "
        $x1 $y1 m
        $x2 $y2 l
        S
        ";
    }


    public function box(
        float $x,
        float $y,
        float $w,
        float $h
    ): void {

        $this->lines[] =
        "
        $x $y m
        " .
        ($x+$w) . " $y l
        " .
        ($x+$w) . " " . ($y+$h) . " l
        $x " . ($y+$h) . " l
        h
        S
        ";
    }



    public function multiline(
        float $x,
        float $startY,
        array $lines,
        float $spacing = 18
    ): void {

        $y = $startY;

        foreach ($lines as $line) {

            $this->text(
                $x,
                $y,
                $line
            );

            $y -= $spacing;
        }
    }



    public function output(
        string $filename="document.pdf"
    ): void {


        $content = implode("\n", $this->lines);


        $this->objects = [];


        // Catalog
        $this->objects[] =
"<<
/Type /Catalog
/Pages 2 0 R
>>";


        // Pages
        $this->objects[] =
"<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>";


        // Page
        $this->objects[] =
"<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 {$this->pageWidth} {$this->pageHeight}]
/Contents 4 0 R

/Resources
<<

/Font
<<

/F1 5 0 R
/F2 6 0 R

>>

>>

>>";


        // Content
        $this->objects[] =
"<<
/Length " . strlen($content) . "
>>
stream
$content
endstream";



        // Helvetica
        $this->objects[] =
"<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>";


        // Helvetica Bold
        $this->objects[] =
"<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica-Bold
>>";



        $pdf="%PDF-1.4\n";


        foreach($this->objects as $i=>$obj){

            $this->offsets[$i+1]=strlen($pdf);

            $pdf .= ($i+1)." 0 obj\n";
            $pdf .= $obj."\n";
            $pdf .= "endobj\n";
        }


        $xref=strlen($pdf);


        $pdf.="xref\n";
        $pdf.="0 ".(count($this->objects)+1)."\n";
        $pdf.="0000000000 65535 f \n";


        foreach($this->offsets as $offset){

            $pdf.=sprintf(
                "%010d 00000 n \n",
                $offset
            );
        }


        $pdf.=
"trailer
<<
/Size ".(count($this->objects)+1)."
/Root 1 0 R
>>
startxref
$xref
%%EOF";


        header(
            "Content-Type: application/pdf"
        );

        header(
            "Content-Disposition: inline; filename=\"$filename\""
        );


        echo $pdf;
        exit;
    }



    private function escape(string $text):string
    {
        return str_replace(
            ["\\","(",")"],
            ["\\\\","\\(","\\)"],
            $text
        );
    }
}