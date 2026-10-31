<?php

namespace App\Services\Reports;

use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportPdfService
{
    public function __construct(private ReportDataAssembler $assembler) {}

    public function download(Store $store, string $type, Carbon $from, Carbon $to): Response|StreamedResponse
    {
        $payload = $this->assembler->assemble($store, $type, $from, $to);
        $filename = sprintf(
            'tanui-%s-report-%s-to-%s.pdf',
            $type,
            $from->format('Ymd'),
            $to->format('Ymd')
        );

        $pdf = Pdf::loadView('admin.reports.pdf.master', $payload)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download($filename);
    }
}
