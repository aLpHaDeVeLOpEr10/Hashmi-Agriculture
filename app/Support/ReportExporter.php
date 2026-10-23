<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportExporter
{
    public function pdf(string $view, array $data, string $filename): Response
    {
        $locale = app()->getLocale();
        app()->setLocale('en');

        try {
            $pdf = Pdf::loadView($view, $data)->setPaper('a4', $data['landscape'] ?? false ? 'landscape' : 'portrait');
            $output = $pdf->output();
        } finally {
            app()->setLocale($locale);
        }

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
        ]);
    }

    /**
     * @param  array<string, array<int, array<int, mixed>>>  $sheets  sheet title => rows; the first row of each sheet is its header
     */
    public function xlsx(array $sheets, string $filename): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new Writer;
        $writer->openToFile($path);
        $header = (new Style)->withFontBold(true);

        $first = true;
        foreach ($sheets as $title => $rows) {
            $sheet = $first ? $writer->getCurrentSheet() : $writer->addNewSheetAndMakeItCurrent();
            $sheet->setName(mb_substr(preg_replace('/[\\\\\/?*\[\]:]/', ' ', $title), 0, 31));
            foreach (array_values($rows) as $i => $row) {
                $writer->addRow($i === 0 ? Row::fromValuesWithStyle($row, $header) : Row::fromValues($row));
            }
            $first = false;
        }
        $writer->close();

        return response()->download($path, $filename.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }
}
