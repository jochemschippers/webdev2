<?php
// app/utils/PdfGenerator.php

namespace App\Utils;

// NEW: Include Dompdf classes
use Dompdf\Dompdf;
use Dompdf\Options;

// IMPORTANT: Dompdf is a Composer package.
// Ensure you have run 'composer require dompdf/dompdf' in your backend directory.
// The Composer autoloader must be included in public/index.php.
// require_once dirname(__FILE__) . '/../vendor/autoload.php'; // This will be handled by index.php's autoloader

class PdfGenerator {

    /**
     * Generates a PDF from HTML content.
     *
     * @param string $html The HTML content to convert to PDF.
     * @param string $filename The desired filename for the PDF (without .pdf extension).
     * @return string|false Returns the PDF content as a binary string, or false on error.
     */
    public static function generatePdf(string $html, string $filename = 'document'): string|false {
        // Configure Dompdf options
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans'); // A font that supports a wide range of Unicode characters
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Enable loading remote images/stylesheets (use with caution)

        // Instantiate Dompdf with options
        $dompdf = new Dompdf($options);

        // Load HTML content
        $dompdf->loadHtml($html);

        // (Optional) Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        try {
            $dompdf->render();

            // Get the generated PDF output
            $output = $dompdf->output();

            error_log("PdfGenerator: Successfully generated PDF: {$filename}.pdf");
            return $output; // Returns the raw PDF binary string
        } catch (\Exception $e) {
            error_log("PdfGenerator: Failed to generate PDF for {$filename}. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generates a PDF and returns it as a base64 encoded string.
     * Useful for embedding PDF data directly in JSON responses.
     *
     * @param string $html The HTML content to convert to PDF.
     * @param string $filename The desired filename for the PDF (without .pdf extension).
     * @return string|false Returns the base64 encoded PDF content, or false on error.
     */
    public static function generatePdfBase64(string $html, string $filename = 'document'): string|false {
        $pdfOutput = self::generatePdf($html, $filename);
        if ($pdfOutput) {
            return base64_encode($pdfOutput);
        }
        return false;
    }
}
