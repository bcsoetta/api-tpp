<?php

namespace App\Http\Controllers;

use App\PNBP;
use App\Printables\PdfPNBP;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PDFController extends ApiController
{
    // show a specific pdf
    public function show(Request $r) {
        try {
            $doctype = $r->get('doc');
            $id = $r->get('id');

            // both must exist, otherwise throw bard request
            if (!$doctype || !$id) {
                throw new BadRequestHttpException("Print request denied. Explain yerself!");
            }

            // build response header
            $headers = [
                'Content-Type'  => 'application/pdf',
            ];

            // if we'got a filename, then force download
            if ($r->get('filename')) {
                // fix the filename to ensure pdf extension
                $filename = $r->get('filename');

                if (strtoupper(substr($filename, -4, 4)) != '.PDF') {
                    $filename .= ".pdf";
                }

                $headers['Content-Disposition'] = "attachment; filename={$filename}";
            }

            // process specific printables
            switch ($doctype) {
                // PNBP?
                case 'pnbp':
                    $pnbp = PNBP::findOrFail($id);
                    $pdf = new PdfPNBP($pnbp);
                    $pdf->printFirstPage();

                    return response($pdf->Output('S'), 200, $headers);

                default:
                    throw new BadRequestException("No printables for document type '{$doctype}'!");
            }
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}
