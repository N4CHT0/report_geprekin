<?php

namespace App\Imports;

use App\Models\M_MarketingBrandMention;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BrandMentionsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['review_text'])) {
            return null;
        }

        return new M_MarketingBrandMention([
            'source'      => $row['source'] ?? 'Excel Import',
            'username'    => $row['username'] ?? 'Anonymous',
            'review_text' => $row['review_text'],
            'status'      => 'Open',
        ]);
    }
}
