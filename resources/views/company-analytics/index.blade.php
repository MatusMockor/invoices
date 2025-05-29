<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Company Analytics Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <analytics-component
                        :analytics-data="{
                            totalRevenue: {{ $statistics['total_income'] ?? 0 }},
                            revenueChange: {{ $statistics['income_change_percentage'] ?? 0 }},
                            totalEarnings: {{ $statistics['current_company_income'] ?? 0 }},
                            totalExpenses: {{ $statistics['current_company_expenses'] ?? 0 }},
                            totalBalance: {{ $statistics['current_company_balance'] ?? 0 }},
                            invoicesPaid: {{ $statistics['paid_invoices_count'] ?? 0 }},
                            invoicesPaidChange: {{ $statistics['paid_invoices_change_percentage'] ?? 0 }},
                            invoicesPending: {{ $statistics['pending_invoices_count'] ?? 0 }},
                            invoicesPendingChange: {{ $statistics['pending_invoices_change_percentage'] ?? 0 }},
                            invoicesOverdue: {{ $statistics['overdue_invoices_count'] ?? 0 }},
                            invoicesOverdueChange: {{ $statistics['overdue_invoices_change_percentage'] ?? 0 }},
                            revenueData: {
                                labels: {{ json_encode($monthlyData['labels'] ?? []) }},
                                values: {{ json_encode($monthlyData['income'] ?? []) }}
                            },
                            expenseData: {
                                labels: {{ json_encode($monthlyData['labels'] ?? []) }},
                                values: {{ json_encode($monthlyData['expenses'] ?? []) }}
                            },
                            topClients: {{ json_encode($statistics['top_clients'] ?? []) }}
                        }"
                    ></analytics-component>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
