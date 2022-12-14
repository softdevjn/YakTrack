<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Session;
use App\Models\ThirdPartyApplication;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index()
    {
        return Inertia::render('Invoice/Index', [
            'invoices' => Invoice::with(['client', 'sessions'])
                ->orderBy('id', 'desc')
                ->get()
                ->map
                ->append('totalDurationForHumans'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Invoice/Edit', [
            'clients' => Client::all(),
        ]);
    }

    public function store()
    {
        request()->validate([
            'number' => 'unique:invoices,number',
        ]);

        $invoice = Invoice::create([
            'number'          => request('number'),
            'date'            => request('date') ?: null,
            'due_date'        => request('due_date') ?: null,
            'amount'          => request('amount') ? intval(floatval(request('amount')) * 100) : null,
            'total_hours'     => request('total_hours'),
            'client_id'       => request('client_id'),
            'is_sent'         => request('is_sent') ?: false,
            'is_paid'         => request('is_paid') ?: false,
            'description'     => request('description') ?? '',
        ]);

        return redirect(route('invoice.index'))
            ->with('success', 'You have created invoice "'.$invoice->number.'"');
    }

    public function edit(Invoice $invoice)
    {
        return Inertia::render('Invoice/Edit', [
            'invoice'  => array_merge($invoice->toArray(), [
                'amount' => $invoice->amount / 100,
            ]),
            'clients'  => Client::all(),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->validate($request, [
            'number'      => [
                'string',
                Rule::unique('invoices')->ignore($invoice->id),
            ],
            'client_id'   => 'exists:clients,id',
            'sessions.*'  => 'exists:sessions,id',
        ]);

        $invoice->update(array_merge($request->only([
            'number',
            'date',
            'due_date',
            'total_hours',
            'client_id',
            'description',
            'is_paid',
            'is_sent',
        ]), [
            'amount' => request('amount') ? intval(floatval(request('amount')) * 100) : $invoice->amount,
        ]));

        $invoice->sessions()->saveMany(Session::findMany($request->sessions));

        $successMessage = 'Invoice '.$invoice->number.' updated.';

        if (
            $request->has('redirectToSessionsScreen') &&
            $request->redirectToSessionsScreen
        ) {
            return redirect()
                ->route('session.index')
                ->with('success', $successMessage);
        }

        return redirect()
            ->route('invoice.index')
            ->with('success', $successMessage);
    }

    public function show(Invoice $invoice)
    {
        return Inertia::render('Invoice/Show', [
            'invoice'                => $invoice->load(['client', 'sessions.task.project.client']),
            'thirdPartyApplications' => ThirdPartyApplication::all(),
        ]);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect(route('invoice.index'));
    }
}
