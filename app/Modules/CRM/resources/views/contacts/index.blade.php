<x-app-layout>
    <crm-module 
        :routes="{
            index: '{{ route('api.crm.contacts.index') }}',
            store: '{{ route('api.crm.contacts.store') }}',
            show: '{{ route('api.crm.contacts.show', ':id') }}',
            update: '{{ route('api.crm.contacts.update', ':id') }}',
            destroy: '{{ route('api.crm.contacts.destroy', ':id') }}',
            bulkDelete: '{{ route('api.crm.contacts.bulk-delete') }}',
            bulkUpdate: '{{ route('api.crm.contacts.bulk-update') }}',
            export: '{{ route('api.crm.contacts.export') }}',
            import: '{{ route('api.crm.contacts.import') }}',
            companies: '{{ route('api.companies.index') }}'
        }"
        :tags-route="'{{ route('api.crm.tags.index') }}'"
        :csrf-token="'{{ csrf_token() }}'"
    ></crm-module>
</x-app-layout>
