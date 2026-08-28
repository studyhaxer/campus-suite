<div>
    <div class="px-4 sm:px-8 py-7">

        <!-- KPI row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @if ($canViewStudents)
                <div class="bg-white border border-[#E2E5EC] rounded-lg p-5">
                    <p class="text-xs text-[#6B7280] font-medium">Total Students</p>
                    <p class="font-mono text-3xl mt-2 font-semibold">{{ number_format($kpi['total_students']) }}</p>
                    <p class="text-xs text-[#6B7280] mt-1.5 font-mono">Active enrollment</p>
                </div>
            @endif

            @if ($canViewFees)
                <div class="bg-white border border-[#E2E5EC] rounded-lg p-5">
                    <p class="text-xs text-[#6B7280] font-medium">Fees Collected — {{ now()->format('F') }}</p>
                    <p class="font-mono text-3xl mt-2 font-semibold">{{ number_format($kpi['fees_collected'], 0) }}</p>
                    <p class="text-xs text-[#C98A3E] mt-1.5 font-mono">{{ number_format($kpi['fees_outstanding'], 0) }} outstanding</p>
                </div>
            @endif

            @if ($canViewAttendance)
                <div class="bg-white border border-[#E2E5EC] rounded-lg p-5">
                    <p class="text-xs text-[#6B7280] font-medium">Attendance Today</p>
                    @if ($kpi['attendance_pct'] !== null)
                        <p class="font-mono text-3xl mt-2 font-semibold">{{ $kpi['attendance_pct'] }}%</p>
                        <p class="text-xs text-[#6B7280] mt-1.5 font-mono">{{ $kpi['attendance_present'] }} of {{ $kpi['attendance_total'] }} present</p>
                    @else
                        <p class="font-mono text-3xl mt-2 font-semibold text-[#9AA1AE]">—</p>
                        <p class="text-xs text-[#6B7280] mt-1.5 font-mono">Not marked yet today</p>
                    @endif
                </div>
            @endif

            @if ($canViewPayroll)
                <div class="bg-white border border-[#E2E5EC] rounded-lg p-5">
                    <p class="text-xs text-[#6B7280] font-medium">Payroll Due — {{ now()->format('F') }}</p>
                    <p class="font-mono text-3xl mt-2 font-semibold">{{ number_format($kpi['payroll_due'], 0) }}</p>
                    <p class="text-xs text-[#6B7280] mt-1.5 font-mono">Not yet paid out</p>
                </div>
            @endif
        </div>

        <!-- Needs Attention + Quick Actions -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white border border-[#E2E5EC] rounded-lg p-5">
                <p class="font-display text-lg mb-3">Needs Attention</p>
                <div class="space-y-2.5">
                    @forelse ($alerts as $alert)
                        <div @class([
                            'flex items-center gap-3 px-3 py-2.5 rounded-md',
                            'bg-[#FBE7E7]' => $alert['level'] === 'danger',
                            'bg-[#FBF0DF]' => $alert['level'] === 'warning',
                            'bg-[#EEF0F4]' => $alert['level'] === 'neutral',
                        ])>
                            <span @class([
                                'w-2 h-2 rounded-full shrink-0',
                                'bg-[#B84C4C]' => $alert['level'] === 'danger',
                                'bg-[#C98A3E]' => $alert['level'] === 'warning',
                                'bg-[#6B7280]' => $alert['level'] === 'neutral',
                            ])></span>
                            <p class="text-sm flex-1">{{ $alert['text'] }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-[#6B7280]">Nothing needs attention right now.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white border border-[#E2E5EC] rounded-lg p-5">
                <p class="font-display text-lg mb-3">Quick Actions</p>
                <div class="space-y-2">
                    @can('create', \App\Models\Student::class)
                        <a href="{{ route('students.index') }}" wire:navigate class="w-full text-left text-sm px-3 py-2.5 rounded-md border border-[#E2E5EC] hover:bg-[#F6F7FA] flex items-center gap-2">
                            <span class="text-[#3A5A9B]">+</span> Add Student
                        </a>
                    @endcan
                    @can('create', \App\Models\StaffProfile::class)
                        <a href="{{ route('staff.index') }}" wire:navigate class="w-full text-left text-sm px-3 py-2.5 rounded-md border border-[#E2E5EC] hover:bg-[#F6F7FA] flex items-center gap-2">
                            <span class="text-[#3A5A9B]">+</span> Add Staff
                        </a>
                    @endcan
                    @can('create', \App\Models\FeeInvoice::class)
                        <a href="{{ route('fees.invoices') }}" wire:navigate class="w-full text-left text-sm px-3 py-2.5 rounded-md border border-[#E2E5EC] hover:bg-[#F6F7FA] flex items-center gap-2">
                            <span class="text-[#3A5A9B]">+</span> Generate Invoices
                        </a>
                    @endcan
                    @can('create', \App\Models\Payslip::class)
                        <a href="{{ route('payroll.index') }}" wire:navigate class="w-full text-left text-sm px-3 py-2.5 rounded-md border border-[#E2E5EC] hover:bg-[#F6F7FA] flex items-center gap-2">
                            <span class="text-[#3A5A9B]">↓</span> Run Payroll
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Campus Registry (Manager only) -->
        @if ($isManager)
            <div class="mt-8 bg-white border border-[#E2E5EC] rounded-lg overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-[#E2E5EC]">
                    <p class="font-display text-lg">Campus Registry</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] uppercase tracking-wider text-[#6B7280] border-b border-[#E2E5EC]">
                                <th class="font-medium px-5 py-2.5">Campus</th>
                                <th class="font-medium px-5 py-2.5 text-right">Students</th>
                                <th class="font-medium px-5 py-2.5 text-right">Attendance</th>
                                <th class="font-medium px-5 py-2.5 text-right">Fees Collected</th>
                                <th class="font-medium px-5 py-2.5 text-right">Payroll Due</th>
                                <th class="font-medium px-5 py-2.5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="font-mono text-[13px]">
                            @php $palette = ['#E7C089', '#6FCF97', '#7FB3D5', '#E08585', '#B39DDB', '#80CBC4', '#F2A65A', '#9FA8DA']; @endphp
                            @foreach ($campusRows as $index => $row)
                                @php
                                    $color = $palette[$row['campus']->id % count($palette)];
                                    $initials = mb_strtoupper(mb_substr(collect(explode(' ', $row['campus']->name))->map(fn ($w) => mb_substr($w, 0, 1))->join(''), 0, 2));
                                @endphp
                                <tr class="border-b border-[#EEF0F4] {{ $index % 2 === 1 ? 'bg-[#FAFBFC]' : '' }}">
                                    <td class="px-5 py-3 font-sans">
                                        <span class="seal w-6 h-6 text-[10px] mr-2" style="color: {{ $color }}">{{ $initials }}</span>{{ $row['campus']->name }}
                                    </td>
                                    <td class="px-5 py-3 text-right">{{ $row['students'] }}</td>
                                    <td class="px-5 py-3 text-right">{{ $row['attendance_pct'] !== null ? $row['attendance_pct'] . '%' : '—' }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($row['fees_collected'], 0) }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($row['payroll_due'], 0) }}</td>
                                    <td class="px-5 py-3 font-sans">
                                        <span @class([
                                            'text-[11px] px-2 py-0.5 rounded-full',
                                            'bg-[#E6F4EC] text-[#2F8F5B]' => $row['status_level'] === 'success',
                                            'bg-[#FBF0DF] text-[#C98A3E]' => $row['status_level'] === 'warning',
                                            'bg-[#FBE7E7] text-[#B84C4C]' => $row['status_level'] === 'danger',
                                        ])>{{ $row['status'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>