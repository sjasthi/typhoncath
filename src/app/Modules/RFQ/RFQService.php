<?php
namespace App\Modules\RFQ;

class RFQService
{
    // Stages that require a quote to be meaningful
    public const QUOTE_REQUIRED_STAGES = ['Quoted', 'Negotiation', 'Won', 'Lost'];

    private RFQRepository $repo;

    public function __construct(RFQRepository $repo)
    {
        $this->repo = $repo;
    }

    // ── Business rule helpers ─────────────────────────────────────────────────

    public function isValidStage(string $stage): bool
    {
        return in_array($stage, RFQRepository::$stages, true);
    }

    public function requiresQuote(string $stage): bool
    {
        return in_array($stage, self::QUOTE_REQUIRED_STAGES, true);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    /** @return string[] */
    public function validateRFQInput(array $input): array
    {
        $errors = [];

        if (trim($input['title'] ?? '') === '')
            $errors[] = 'Title is required.';

        if (trim($input['account_id'] ?? '') === '' && trim($input['contact_id'] ?? '') === '')
            $errors[] = 'At least one of Account or Contact is required.';

        if (!$this->isValidStage($input['stage'] ?? ''))
            $errors[] = 'Invalid stage selected.';

        return $errors;
    }

    /** @return string[] */
    public function validateQuoteInput(array $input): array
    {
        $errors = [];

        if (!is_numeric($input['rfq_id'] ?? '') || (int)$input['rfq_id'] <= 0)
            $errors[] = 'Invalid RFQ.';

        if (!isset($input['quote_amount']) || !is_numeric($input['quote_amount']) || trim($input['quote_amount']) === '')
            $errors[] = 'Quote amount is required and must be a number.';

        return $errors;
    }

    /** @return string[] */
    public function validateReservationInput(array $input): array
    {
        $errors = [];

        if (!is_numeric($input['rfq_id'] ?? '') || (int)$input['rfq_id'] <= 0)
            $errors[] = 'Invalid RFQ.';

        if (trim($input['product_id'] ?? '') === '')
            $errors[] = 'Product is required.';

        if (!isset($input['quantity_reserved']) || (int)$input['quantity_reserved'] < 1)
            $errors[] = 'Quantity must be at least 1.';

        return $errors;
    }

    // ── Operations ────────────────────────────────────────────────────────────

    /**
     * Create an RFQ, optionally attaching an initial quote and one or more
     * inventory reservations in a single logical operation.
     *
     * @param array       $rfqData      Validated RFQ fields (title, account_id, …)
     * @param array|null  $quoteData    Optional quote fields (quote_amount, discount, …)
     * @param array       $reservations Array of ['product_id' => x, 'quantity_reserved' => y]
     * @param int         $userId       ID of the authenticated user creating the RFQ
     * @return int New RFQ ID
     */
    public function createRFQ(array $rfqData, ?array $quoteData, array $reservations, int $userId): int
    {
        $rfqId = $this->repo->insert([
            'title'              => $rfqData['title'],
            'account_id'         => ($rfqData['account_id'] ?? '') !== '' ? (int)$rfqData['account_id'] : null,
            'contact_id'         => ($rfqData['contact_id'] ?? '') !== '' ? (int)$rfqData['contact_id'] : null,
            'description'        => $rfqData['description'] ?? '',
            'stage'              => $rfqData['stage'],
            'created_by_user_id' => $userId,
        ]);

        if ($quoteData !== null && is_numeric($quoteData['quote_amount'] ?? '')) {
            $this->repo->insertQuote($rfqId, $quoteData);
        }

        foreach ($reservations as $res) {
            if (($res['product_id'] ?? '') !== '' && (int)($res['quantity_reserved'] ?? 0) >= 1) {
                $this->repo->insertReservation($rfqId, $res);
            }
        }

        return $rfqId;
    }

    /**
     * Update the core fields of an existing RFQ.
     */
    public function updateRFQ(int $rfqId, array $data): void
    {
        $this->repo->update($rfqId, [
            'title'       => $data['title'],
            'account_id'  => ($data['account_id'] ?? '') !== '' ? (int)$data['account_id'] : null,
            'contact_id'  => ($data['contact_id'] ?? '') !== '' ? (int)$data['contact_id'] : null,
            'description' => $data['description'] ?? '',
            'stage'       => $data['stage'],
        ]);
    }

    /**
     * Change only the stage of an RFQ.
     * This is the single place to add stage-transition side-effects in future
     * (e.g. release inventory on Lost, convert reservations on Won).
     */
    public function changeStage(int $rfqId, string $stage): void
    {
        $this->repo->updateStage($rfqId, $stage);
    }

    /**
     * Attach a quote to an existing RFQ.
     */
    public function addQuote(int $rfqId, array $data): void
    {
        $this->repo->insertQuote($rfqId, [
            'quote_amount'        => $data['quote_amount'],
            'discount'            => $data['discount']            ?? '',
            'validity_start_date' => $data['validity_start_date'] ?? '',
            'validity_end_date'   => $data['validity_end_date']   ?? '',
        ]);
    }

    /**
     * Reserve inventory for an RFQ (single item).
     * Inventory counts are synced inside the repository.
     */
    public function addReservation(int $rfqId, array $data): void
    {
        $this->repo->insertReservation($rfqId, [
            'product_id'        => $data['product_id'],
            'quantity_reserved' => $data['quantity_reserved'],
        ]);
    }
}
