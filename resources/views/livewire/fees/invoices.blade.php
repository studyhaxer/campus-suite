<div>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl text-gray-800">Fee Invoices</h2>
                @can('create', \App\Models\FeeInvoice::class)
                    <button wire:click="generateInvoices" wire:confirm="Generate invoices for this month for all active students without one?" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        Generate Invoices
                    </button>
                @endcan
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-md">{{ session('status') }}</div>
            @endif

            <div class="mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input type="month" wire:model.live="month" class="rounded-md border-gray-300 shadow-sm text-sm">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search student or admission #" class="rounded-md border-gray-300 shadow-sm text-sm">
                <select wire:model.live="classSectionFilter" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">All Classes</option>
                    @foreach ($classSections as $section)
                        <option value="{{ $section->id }}">{{ $section->fullName }}</option>
                    @endforeach
                </select>
                <select wire:model.live="statusFilter" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">All Statuses</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                </select>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($invoices as $invoice)
                                <tr wire:key="invoice-{{ $invoice->id }}">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $invoice->student->full_name }}
                                        <div class="text-xs text-gray-400 font-mono">{{ $invoice->student->admission_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice->student->classSection?->fullName ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ number_format($invoice->amount, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ number_format($invoice->amount_paid, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ number_format($invoice->balance, 2) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span @class([
                                            'px-2 py-1 text-xs rounded-full',
                                            'bg-green-100 text-green-700' => $invoice->status === 'paid',
                                            'bg-yellow-100 text-yellow-700' => $invoice->status === 'partial',
                                            'bg-red-100 text-red-700' => $invoice->status === 'unpaid',
                                        ])>
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right">
                                        @can('create', \App\Models\FeePayment::class)
                                            @if ($invoice->status !== 'paid')
                                                <button wire:click="openPay({{ $invoice->id }})" class="text-indigo-600 hover:text-indigo-800">Collect Payment</button>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No invoices for this month yet. Click "Generate Invoices" to create them.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t">{{ $invoices->links() }}</div>
            </div>

        </div>
    </div>

    @if ($showPayModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click.self="$set('showPayModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Collect Payment</h3>
                    <form wire:submit="recordPayment" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" step="0.01" wire:model="payAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('payAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Date</label>
                            <input type="date" wire:model="payDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('payDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Method</label>
                            <select wire:model="payMethod" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <input type="text" wire:model="payNotes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" wire:click="$set('showPayModal', false)" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Record Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>