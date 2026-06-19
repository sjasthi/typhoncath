<?php
namespace App\Modules\RFQ;

use App\Core\Database;

class RFQRepository
{

    public function all(): array{   
        // TODO: Replace with module-specific prepared queries.
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM rfqs ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function find_by_id($id){
        $db = Database::connection();
        // IMPORTANT THE '?' PREVENT SQL INJECTION AND ALLOWS FOR US TO CHECK THE 
        // VALUE PASSED IN BEFORE RUNNING IT ON THE DB
        $stmt = $db->prepare("SELECT FROM rfqs WHERE id==?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }   
}
