<?php

return [
    // Laravel's file validation limit is measured in kilobytes.
    'book_pdf_max_kb' => (int) env('BOOK_PDF_MAX_KB', 102400),
];
