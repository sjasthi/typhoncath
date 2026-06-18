<?php
namespace App\Core;


// This file loads view files. (found in modules)
// This keeps controller logic separate from HTML.

// Instead of putting database queries, business rules, and HTML all in one giant PHP file, the flow becomes:

// Controller gets request
// Service handles rules
// Repository gets data
// View displays the page




// Example:

// View::render('RFQ/views/pipeline_board.php', [
//     'rfqs' => $rfqs
// ]);

// Inside pipeline_board.php, you can use:

// foreach ($rfqs as $rfq) {
//     echo $rfq['title'];
// }

// Because this line:

// extract($data);

// turns this:

// ['rfqs' => $rfqs]

// into this variable:

// $rfqs
class View
{
    public static function render(string $path, array $data = []): void
    {
        extract($data);
        include __DIR__ . '/../Modules/' . $path;
    }
}
