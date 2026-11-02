<?php

namespace App\Http\Controllers;

use App\Models\CourseClient;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function telecharger(Request $request, CourseClient $courseClient)
    {
        $courseClient->load(['course.vehicle', 'course.company', 'client']);
        $course = $courseClient->course;

        abort_unless($course->company_id === $request->user()->company_id, 404);
        abort_unless($course->statut === 'livree', 403, "La facture n'est disponible qu'une fois la course livrée.");

        $invoice = Invoice::emettre($courseClient);

        return Pdf::loadView('factures.pdf', [
            'invoice' => $invoice,
            'courseClient' => $courseClient,
            'course' => $course,
            'company' => $course->company,
            'client' => $courseClient->client,
        ])->download($invoice->numero.'.pdf');
    }
}
