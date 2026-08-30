<div>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl text-gray-800">Payroll</h2>
                @can('create', \App\Models\Payslip::class)
                    <button wire:click="generatePayslips"
                        wire:confirm="Generate payslips for this month for all active staff without one?"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        Generate Payslips
                    </button>
                @endcan
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-md">{{ session('status') }}</div>
            @endif

            <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input type="month" wire:model.live="month" class="rounded-md border-gray-300 shadow-sm text-sm">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search staff name"
                    class="rounded-md border-gray-300 shadow-sm text-sm">
                <select wire:model.live="statusFilter" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="paid">Paid</option>
                </select>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Designation
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Base</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adjustments
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($payslips as $payslip)
                                <tr wire:key="payslip-{{ $payslip->id }}">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $payslip->staff->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $payslip->staff->staffProfile?->designation ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ number_format($payslip->base_salary, 2) }}</td>
                                    <td
                                        class="px-6 py-4 text-sm {{ $payslip->adjustments < 0 ? 'text-red-600' : ($payslip->adjustments > 0 ? 'text-green-600' : 'text-gray-500') }}">
                                        {{ $payslip->adjustments >= 0 ? '+' : '' }}{{ number_format($payslip->adjustments, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ number_format($payslip->net_amount, 2) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span
                                            class="px-2 py-1 text-xs rounded-full {{ $payslip->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ ucfirst($payslip->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right space-x-3">
                                        @can('update', $payslip)
                                            @if ($payslip->status === 'draft')
                                                <button wire:click="openAdjust({{ $payslip->id }})"
                                                    class="text-indigo-600 hover:text-indigo-800">Adjust</button>
                                                <button wire:click="markPaid({{ $payslip->id }})"
                                                    wire:confirm="Mark this payslip as paid?"
                                                    class="text-green-600 hover:text-green-800">Mark Paid</button>
                                            @endif
                                        @endcan
                                        <button wire:click="downloadPdf({{ $payslip->id }})"
                                            class="text-gray-500 hover:text-gray-700">PDF</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No payslips for this month yet. Click "Generate Payslips" to create them.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t">{{ $payslips->links() }}</div>
            </div>

        </div>
    </div>

    @if ($showAdjustModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            wire:click.self="$set('showAdjustModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Adjust Payslip</h3>
                    <p class="text-sm text-gray-500 mb-4">Enter a positive amount for a bonus, or a negative amount for
                        a deduction.</p>
                    <form wire:submit="saveAdjustment" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Adjustment Amount</label>
                            <input type="number" step="0.01" wire:model="adjustAmount"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('adjustAmount')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <input type="text" wire:model="adjustNotes" placeholder="e.g. Late penalty, Eid bonus"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" wire:click="$set('showAdjustModal', false)"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Save
                                Adjustment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
